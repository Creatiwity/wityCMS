<?php 
/**
 * WResponse.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WResponse does...
 *
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.4-19-05-2013
 */
class WResponse {
	private $tpl;
	
	private $themeName;
	private $themeDir;
	
	public function __construct($theme) {
		$this->setTheme($theme);
		$this->tpl = WSystem::getTemplate();
	}
	
	/**
	 * Assigns a theme
	 * 
	 * @param string $theme theme name (must a be an existing directory in /themes/)
	 */
	public function setTheme($theme) {
		if ($theme == '_blank') {
			$this->themeName = '_blank';
		} else if (is_dir(THEMES_DIR.$theme)) {
			$this->themeName = $theme;
			$this->themeDir = str_replace(WITY_PATH, '', THEMES_DIR).$theme.DS;
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
		return $this->themeName;
	}
	
	/**
	 * Final render of the response
	 * Displays a valid HTML5 to the screen
	 */
	public function render(WView $view) {
		// Flush the notes waiting for their own view
		if (WNote::displayPlainView()) {
			return false;
		}
		
		// Check theme
		if (empty($this->themeName) && WNote::count('response_theme') == 0) {
			WNote::error('response_theme', "WResponse::render(): No theme given or it was not found.", 'plain');
			return false;
		}
		
		// Select Theme main template
		if ($this->themeName == '_blank') {
			$themeMainFile = str_replace(WITY_PATH, '', THEMES_DIR).'system'.DS.'_blank.html';
		} else {
			$themeMainFile = str_replace(WITY_PATH, '', THEMES_DIR).$this->themeName.DS.'templates'.DS.'index.html';
		}
		
		// Handle notes
		$this->tpl->assign('notes', WNote::parse(WNote::get('*')));
		
		// Define {$include} tpl's var
		$this->tpl->assign('include', $view->getTemplate());
		
		$dir = WRoute::getDir();
		if (empty($dir)) {
			// Direct render
			try {
				$this->tpl->display($themeMainFile);
			} catch (Exception $e) {
				WNote::error('response_tpl_display', $e->getMessage(), 'die');
				return false;
			}
		} else {
			// Absolute links fix
			// If $dir is not the root file, then change links
			try {
				$html = $this->tpl->parse($themeMainFile);
				echo str_replace(
					array('src="/', 'href="/', 'action="/', 'data-link-modal="/'),
					array('src="'.$dir.'/', 'href="'.$dir.'/', 'action="'.$dir.'/', 'data-link-modal="'.$dir.'/'),
					$html
				);
			} catch (Exception $e) {
				WNote::error('response_tpl_parse', $e->getMessage(), 'die');
				return false;
			}
		}
	}
}

?>