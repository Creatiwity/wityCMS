<?php 
/**
 * WResponse.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WResponse compiles the final render of WityCMS that will be sent to the browser.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-27-05-2013
 */
class WResponse {
	private $handler;
	
	public function __construct($mode, $param = '') {
		switch ($mode) {
			case 'm':
				
				break;
			
			case 'v':
				
				break;
			
			case 'mv':
				
				break;
			
			case 'theme':
			default:
				require_once SYS_DIR.'WResponse'.DS.'WResponseTheme.php';
				$this->handler = new WResponseTheme($param);
				break;
		}
	}
	
	/**
	 * Final render of the response
	 * Displays a valid HTML5 to the screen
	 * 
	 * @param WView $view The view to render as a main instance
	 */
	public function render(WView $view) {
		// Render headers
		if ($this->handler->renderHeaders($view->getHeaders())) {
			return true;
		}
		
		try {
			$notes = WNote::get('*');
			
			$response = $this->handler->render($view, $notes);
		} catch (Exception $e) {
			WNote::error('response_final_render', $e->getMessage(), 'die');
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
		
		// Global var
		
		
		echo $response;
		
		return true;
	}
}

?>
