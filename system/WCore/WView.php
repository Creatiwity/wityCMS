<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version $Id: WCore/WView.php 0002 21-07-2012 Fofif $
 * @package Wity
 */

/**
 * WView
 * 
 * This class handles application's response
 */

class WView {
	// State variable telling whether the view was already rendered
	private static $response_sent = false;
	
	// Instance of WTemplate
	public $tpl;
	
	// Theme to be loaded
	private $themeName = '';
	private $themeDir = '';
	
	// Template response file to display as output
	private $responseFile = '';
	
	// Variables with a special treatment
	private $specialVars = array('css', 'js');
	
	// Template variables
	private $vars = array();
	
	public function __construct() {
		$this->tpl = WSystem::getTemplate();
		
		// Default vars
		$site_name = WConfig::get('config.site_name');
		$this->assign('site_name', $site_name);
		$this->assign('page_title', $site_name);
	}
	
	/**
	 * Assign a theme
	 * Must a be an existing directory in /themes/
	 */
	public function setTheme($theme) {
		if ($theme == '_blank') {
			$this->themeName = '_blank';
		} else if (is_dir(THEMES_DIR.$theme)) {
			$this->themeName = $theme;
			$this->themeDir = THEMES_DIR.$theme.DS;
		} else {
			WNote::error('view_error_theme', "WView::setTheme(): The theme \"".$theme."\" does not exist.");
		}
	}
	
	public function getTheme() {
		return $this->themeName;
	}
	
	public function setResponse($file) {
		if (file_exists($file)) {
			$this->responseFile = $file;
		} else {
			WNote::error('view_error_response', "WView::setResponse(): The response file \"".$file."\" does not exist.");
		}
	}
	
	/**
	 * Recherche un fichier template en fonction du nom de l'appli et de l'action
	 * Le fichier sera cherché en priorité dans les fichiers du thème puis dans les fichiers de l'appli
	 * 
	 * @return string adresse du fichier
	 */
	public function findResponse($appName, $action, $adminLoaded) {
		if ($adminLoaded) {
			$this->setResponse(APPS_DIR.$appName.DS.'admin'.DS.'templates'.DS.$action.'.html');
		} else {
			$themeTplHref = $this->themeDir.'templates'.DS.$appName.DS.$action.'.html';
			if (file_exists($themeTplHref)) {
				$this->setResponse($themeTplHref);
			} else {
				$this->setResponse(APPS_DIR.$appName.DS.'front'.DS.'templates'.DS.$action.'.html');
			}
		}
	}
	
	public function assignOne($name, $value) {
		// Is $name a Special var?
		if (in_array($name, $this->specialVars)) {
			if (!isset($this->vars[$name])) {
				$this->vars[$name] = array($value);
			} else if (!in_array($value, $this->vars[$name])) {
				$this->vars[$name][] = $value;
			}
		} else { // Normal case
			$this->vars[$name] = $value;
		}
	}
	
	public function assign($a, $b = null) {
		if (is_string($a)) {
			$this->assignOne($a, $b);
		} else if (is_array($a)) {
			foreach ($a as $key => $value) {
				$this->assignOne($key, $value);
			}
		}
	}
	
	public function assignBlock($blockName, $value) {
		if (!isset($this->vars[$blockName.'_block'])) {
			$this->vars[$blockName.'_block'] = array($value);
		} else {
			$this->vars[$blockName.'_block'][] = $value;
		}
	}
	
	/**
	 * Retourne une variable en "stack" avec un traitement particulier
	 * 
	 * @param $stack_name Nom du stack
	 * @return string
	 */
	public function getStack($stack_name) {
		if (empty($this->vars[$stack_name])) {
			return '';
		}
		
		switch ($stack_name) {
			case 'css':
				$css = $this->tpl->getVar('css');
				foreach ($this->vars['css'] as $file) {
					$css .= sprintf(
						'<link href="%s%s" rel="stylesheet" type="text/css" />'."\n", 
						(dirname($file) == '.') ? THEMES_DIR.$this->themeName.DS.'css'.DS : '',
						$file
					);
				}
				return $css;
				break;
			
			case 'js':
				$script = $this->tpl->getVar('js');
				foreach ($this->vars['js'] as $file) {
					$script .= sprintf(
						'<script type="text/javascript" src="%s%s"></script>'."\n", 
						(dirname($file) == '.') ? THEMES_DIR.$this->themeName.DS.'js'.DS : '',
						$file
					);
				}
				return $script;
				break;
			
			default:
				return $this->tpl->getVar($stack_name).$this->vars[$stack_name];
				break;
		}
	}
	
	/**
	 * Render the view
	 */
	public function render() {
		// Check if no previous view has already been rendered
		if (self::$response_sent) {
			// HTML sent => abort
			return;
		}
		
		// Check theme
		if (empty($this->themeName) && WNote::count('view_error_theme') == 0) {
			WNote::error('view_error_theme', "WView::render(): No theme given or it was not found.");
		}
		// Check response file
		if (empty($this->responseFile) && WNote::count('view_error_response') == 0) {
			WNote::error('view_error_response', "WView::render(): No response file given.");
		}
		
		// Handle errors
		$view_errors = WNote::get('view_error*');
		if (!empty($view_errors)) {
			WNote::displayFull($view_errors);
			return;
		}
		if ($this->getTheme() != '_blank') {
			$this->assign('notes', WNote::parse(WNote::get('*')));
		}
		
		// Treat "special vars"
		foreach ($this->specialVars as $stack) {
			if (!empty($this->vars[$stack])) {
				$this->vars[$stack] = $this->getStack($stack);
			} else {
				unset($this->vars[$stack]);
			}
		}
		
		// Assign View variables
		$this->tpl->assign($this->vars);
		
		if ($this->themeName == '_blank') {
			$themeMainFile = $this->responseFile;
		} else {
			// Define {$include} tpl's var
			$this->tpl->assign('include', $this->responseFile);
			
			$themeMainFile = $this->themeDir.DS.'templates'.DS.'index.html';
		}
		
		$base = WRoute::getDir();
		if ($base == '/') {
			// Direct render
			try {
				$this->tpl->display($themeMainFile);
			} catch (Exception $e) {
				WNote::displayFull(array(WNote::error('view_error_tpl_display', $e->getMessage(), 'ignore')));
			}
		} else {
			// Absolute links fix
			// If $base is not the root file, then change links
			try {
				$html = $this->tpl->parse($themeMainFile);
				echo str_replace(
					array('src="/', 'href="/', 'action="/'),
					array('src="'.$base, 'href="'.$base, 'action="'.$base),
					$html
				);
			} catch (Exception $e) {
				WNote::displayFull(array(WNote::error('view_error_tpl_parse', $e->getMessage(), 'ignore')));
			}
		}
		
		// Mark the view as rendered
		self::$response_sent = true;
	}
}
?>