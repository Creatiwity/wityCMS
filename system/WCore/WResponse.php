<?php 
/**
 * WResponse.php
 */

defined('IN_WITY') or die('Access denied');

interface WResponseMode {
	public function setModel($model);
	public function renderHeaders();
	public function render($notes);
}

/**
 * WResponse compiles the final render of WityCMS that will be sent to the browser.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-16-10-2013
 */
class WResponse {
	/**
	 * @var WResponseMode Response's handler to manage the final render
	 */
	private $handler = null;
	
	/**
	 * Finds the response handler and load it.
	 * 
	 * @param string $mode   Response's mode: theme, m, v, mv, ...
	 * @param mixed  $option Response's option: theme name for instance
	 */
	public function __construct($mode, $option = null) {
		switch ($mode) {
			case 'm':
			case 'v':
			case 'mv':
				require_once SYS_DIR.'WResponse'.DS.'WResponseSerialize.php';
				$this->handler = new WResponseSerialize($mode);
				break;
			
			case 'theme':
				require_once SYS_DIR.'WResponse'.DS.'WResponseTheme.php';
				$this->handler = new WResponseTheme($option);
				break;
			
			default:
				$response_class = 'WResponse'.ucfirst(strtolower($mode));
				$response_file  = SYS_DIR.'WResponse'.DS.$response_class.'.php';
				
				include_once $response_file;
				// The response plugin must implement WResponseMode
				if (class_exists($response_class) && in_array('WResponseMode', class_implements($response_class))) {
					$this->handler = new $response_class($option);
				} else {
					require_once SYS_DIR.'WResponse'.DS.'WResponseTheme.php';
					$this->handler = new WResponseTheme($option);
				}
				break;
		}
	}
	
	/**
	 * Checks if a mode is valid: a handler have to be found for this mode
	 * in system/WResponse/ directory.
	 * 
	 * @param $mode string Mode name
	 * @return bool
	 */
	public static function isMode($mode) {
		if (in_array($mode, array('theme', 'm', 'v', 'mv'))) {
			return true;
		} else {
			$response_class = 'WResponse'.ucfirst(strtolower($mode));
			$response_file  = SYS_DIR.'WResponse'.DS.$response_class.'.php';
			
			return file_exists($response_file);
		}
	}
	
	/**
	 * Renders the final response to the client.
	 * Response can be a valid HTML5 string with the WityCMS theme or, it can be a JSON structure for instance.
	 * 
	 * @param array $model Model to be rendered (the view will be calculated in the plugin)
	 */
	public function render(array $model) {
		// Assigns the model to the response plugin
		$this->handler->setModel($model);
		
		// Render headers
		if ($this->handler->renderHeaders()) {
			return true;
		}
		
		try {
			$notes = WNote::get('*');
			
			$response = $this->handler->render($notes);
		} catch (Exception $e) {
			WNote::error('response_final_render', "An error was encountered during the final response rendering: ".$e->getMessage(), 'die');
			return false;
		}
		
		// Absolute links fix
		// If $dir is not the root file, then change links
		$dir = WRoute::getDir();
		if (!empty($dir)) {
			$response = str_replace(
				array('src="/', 'href="/', 'action="/', 'data-link-modal="/'),
				array('src="'.$dir.'/', 'href="'.$dir.'/', 'action="'.$dir.'/', 'data-link-modal="'.$dir.'/'),
				$response
			);
		}
		
		echo $response;
	}
}

?>
