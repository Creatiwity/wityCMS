<?php 
/**
 * WResponse.php
 */

defined('IN_WITY') or die('Access denied');

interface WResponseMode {
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
	 * @var WTemplate Instance of WTemplate
	 */
	private $tpl;
	
	/**
	 * @var string Name of the theme used for the response
	 */
	private $theme_name;
	
	/**
	 * @var string Directory of the theme used for the response
	 */
	private $theme_dir;
	
	/**
	 * Assigns a theme
	 * 
	 * @param string $theme theme name (must a be an existing directory in /themes/)
	 */
	public function setTheme($theme) {
		if ($theme == '_blank') {
			$this->theme_name = '_blank';
			$this->theme_dir = 'themes/system/';
		} else if (is_dir(THEMES_DIR.$theme)) {
			$this->theme_name = $theme;
			$this->theme_dir = str_replace(WITY_PATH, '', THEMES_DIR).$theme.DS;
		} else {
			WNote::error('view_set_theme', "WView::setTheme(): The theme \"".$theme."\" does not exist.", 'plain');
		}
	}
	
	/**
	 * Returns current theme name
	 * 
	 * @return string current theme name
	 */
	public function getTheme() {
		return $this->theme_name;
	}
	
	/**
	 * Renders the final response to the client.
	 * Response can be a valid HTML5 string with the WityCMS theme or, it can be a JSON structure for instance.
	 * 
	 * @param array $model Model to be rendered (the view will be calculated in the plugin)
	 */
	public function render(WView $view = null, $theme) {
		// Check headers
		$headers = $view->getHeaders();
		foreach ($headers as $name => $value) {
			header($name.': '.$value);
		}
		if (isset($headers['location'])) {
			return true;
		}
		
		// Load WTemplate
		$this->tpl = WSystem::getTemplate();
		if (is_null($this->tpl)) {
			throw new Exception("WResponse::__construct(): WTemplate cannot be loaded.");
		}
		
		// Default vars
		$site_name = WConfig::get('config.site_name');
		$this->tpl->assign('site_name', $site_name);
		$this->tpl->assign('page_title', $site_name);
		
		$this->setTheme($theme);
		
		// Check theme
		if (empty($this->theme_name)) {
			WNote::error('response_theme', "WResponse::render(): No theme given or it was not found.", 'plain');
		}
		
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
			$this->setTheme('_blank');
		}
		
		// Select Theme main template
		if ($this->theme_name == '_blank') {
			$themeMainFile = $this->theme_dir.'_blank.html';
		} else {
			$themeMainFile = $this->theme_dir.'templates'.DS.'index.html';
		}
		
		try {
			// Define {$include} tpl's var
			$this->tpl->assign('include', $view->render());
			
			// Handle notes
			$this->tpl->assign('notes', WNote::getView(WNote::get('*'))->render());
			
			$html = $this->tpl->parse($themeMainFile);
			
			// Absolute links fix
			echo $this->absoluteLinkFix($html);
		} catch (Exception $e) {
			WNote::error('response_final_render', "An error was encountered during the final response rendering: ".$e->getMessage(), 'die');
			return false;
		}
		
		return true;
	}
	
	/**
	 * Fixes absolute links.
	 * If WRoute::getDir() is not the root file, then change links.
	 * 
	 * @param string $string A string containing root HTML links
	 * @return string
	 */
	public static function absoluteLinkFix($string) {
		$dir = WRoute::getDir();
		if (!empty($dir)) {
			$string = str_replace(
				array('src="/', 'href="/', 'action="/', 'data-link-modal="/'),
				array('src="'.$dir.'/', 'href="'.$dir.'/', 'action="'.$dir.'/', 'data-link-modal="'.$dir.'/'),
				$string
			);
		}
		
		return $string;
	}
	
	public function renderModel(array $model) {
		// Store the plain notes
		$plain_notes = WNote::getPlain();
		if (!empty($plain_notes)) {
			$model['result'] = $plain_notes;
		}
		
		// Store the notes
		$model['notes'] = WNote::get('*');
		
		echo str_replace('\\/', '/', json_encode($model));
		
		return true;
	}
	
	public function renderView(array $model, WView $view) {
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
		}
		
		// Store the notes
		$model['notes'] = WNote::get('*');
		
		// Remove the application's result
		unset($model['result']);
		
		$model['view'] = $view->render();
		
		// Absolute link fix
		$model['view'] = $this->absoluteLinkFix($model['view']);
		
		echo str_replace('\\/', '/', json_encode($model));
		
		return true;
	}
	
	public function renderModelView(array $model, WView $view) {
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
		}
		
		// Store the notes
		$model['notes'] = WNote::get('*');
		
		$model['view'] = $view->render();
		
		// Absolute link fix
		$model['view'] = $this->absoluteLinkFix($model['view']);
		
		echo str_replace('\\/', '/', json_encode($model));
		
		return true;
	}
}

?>
