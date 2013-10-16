<?php 
/**
 * WResponseTheme.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WResponseTheme is a plugin for WResponse.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-16-10-2013
 */
class WResponseTheme implements WResponseMode {
	private $model;
	private $view;
	
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
	
	public function __construct($theme) {
		if (!empty($theme)) {
			$this->setTheme($theme);
		} else {
			$this->setTheme(WConfig::get('config.theme'));
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
	}
	
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
	
	public function setModel($model) {
		$this->model = $model;
		$this->view = WRetriever::getViewFromModel($this->model);
	}
	
	public function renderHeaders() {
		$headers = $this->view->getHeaders();
		foreach ($headers as $name => $value) {
			header($name.': '.$value);
		}
		
		if (isset($headers['location'])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Final render of the response
	 * Displays a valid HTML5 to the screen
	 * 
	 * @param WView $view The view to render as a main instance
	 */
	public function render($notes) {
		$view_theme = $this->view->getTheme();
		if (!empty($view_theme)) {
			$this->setTheme($view_theme);
		}
		
		// Check theme
		if (empty($this->theme_name)) {
			WNote::error('response_theme', "WResponse::render(): No theme given or it was not found.", 'plain');
		}
		
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			$view = $plain_view;
			$this->setTheme('_blank');
		} else {
			$view = $this->view;
		}
		
		// Select Theme main template
		if ($this->theme_name == '_blank') {
			$themeMainFile = $this->theme_dir.'_blank.html';
		} else {
			$themeMainFile = $this->theme_dir.'templates'.DS.'index.html';
		}
		
		// Define {$include} tpl's var
		$this->tpl->assign('include', $view->render());
		
		// Handle notes
		$this->tpl->assign('notes', WNote::getView($notes)->render());
		
		$html = $this->tpl->parse($themeMainFile);
		
		// Absolute links fix
		return WResponse::absoluteLinkFix($html);
	}
}

?>
