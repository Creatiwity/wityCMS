<?php 
/**
 * WResponse.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WResponse compiles the final render of WityCMS that will be sent to the browser.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-28-10-2013
 */
class WResponse {
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
	 * @param string $theme Theme name (must a be an existing directory in /themes/)
	 */
	public function setTheme($theme) {
		if ($theme == '_blank') {
			$this->theme_name = '_blank';
			$this->theme_dir = 'themes/system/';
		} else if (is_dir(THEMES_DIR.$theme)) {
			$this->theme_name = $theme;
			$this->theme_dir = str_replace(WITY_PATH, '', THEMES_DIR).$theme.DS;
		} else {
			WNote::error('response_set_theme', "WResponse::setTheme(): The theme \"".$theme."\" does not exist.", 'plain');
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns current theme name
	 * 
	 * @return string Current theme name
	 */
	public function getTheme() {
		return $this->theme_name;
	}
	
	/**
	 * WityCMS's classic render with HTML theme.
	 * 
	 * @param WView  $view  View to be rendered
	 * @param string $theme Theme name to use to wrap the view
	 */
	public function render(WView $view = null, $default_theme, $model = array()) {
		// Check headers
		if (isset($model['headers'])) {
			foreach ($model['headers'] as $name => $value) {
				header($name.': '.$value);
			}
			
			if (isset($model['headers']['location'])) {
				if (is_array($model['result']) && array_keys($model['result']) == array('level', 'code', 'message', 'handlers')) {
					WNote::raise($model['result']);
				}
				
				return true;
			}
		}
		
		// Load WTemplate
		$tpl = WSystem::getTemplate();
		if (is_null($tpl)) {
			throw new Exception("WResponse::render(): WTemplate cannot be loaded.");
		}
		
		// Default template variables
		$tpl->assign('base_url', WRoute::getBase());
		
		$site_name = WConfig::get('config.site_name');
		$tpl->assign('site_name', $site_name);
		
		$page_title = $tpl->getVar('page_title');
		if (empty($page_title)) { // Do not overwrite a value set by user
			$tpl->assign('page_title', $site_name);
		}
		
		// Load in priority theme asked by the view
		$view_theme = $view->getTheme();
		if (empty($view_theme) || !$this->setTheme($view_theme)) {
			$this->setTheme($default_theme);
		}
		
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
			$tpl->assign('include', $view->render());
			
			// Handle notes
			$tpl->assign('notes', WNote::getView(WNote::get('*'))->render());
			
			$html = $tpl->parse($themeMainFile);
			
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
	
	/**
	 * Renders a model into a JSON view.
	 * 
	 * @param array $model Main application's model to display
	 */
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
	
	/**
	 * Renders the main application's view into a JSON structure, without the application's result.
	 * 
	 * @param array $model
	 * @param WView $view
	 */
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
	
	/**
	 * Renders the main application's model + view into a JSON structure.
	 * 
	 * @param array $model
	 * @param WView $view
	 */
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
