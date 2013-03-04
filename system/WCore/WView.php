<?php 
/**
 * WView.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WView handles application's response
 * 
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-17-01-2013
 */
class WView {
	
	/**
	 * @var boolean State variable telling whether the view was already rendered
	 */
	private static $response_sent = false;
	
	/**
	 * @var array Context of the application describing app's name, app's directory and app's main class
	 */
	private $context;
	
	/**
	 * @var WTemplate Instance of WTemplate
	 */
	public $tpl;
	
	/**
	 * @var string Theme name to be loaded
	 */
	private $themeName = '';
	
	/**
	 * @var string Theme directory
	 */
	private $themeDir = '';
	
	/**
	 * @var string Template response file to display as output
	 */
	private $responseFile = '';
	
	/**
	 * @var array Variables with a special treatment like "css" and "js"
	 */
	private $specialVars = array('css', 'js');
	
	/**
	 * @var array Template variables
	 */
	private $vars = array();
	
	/**
	 * Setup template
	 */
	public function __construct() {
		$this->tpl = WSystem::getTemplate();
		
		// Default vars
		$site_name = WConfig::get('config.site_name');
		$this->assign('site_name', $site_name);
		$this->assign('page_title', $site_name);
	}
	
	/**
	 * Defines the context of the application this View belongs to
	 * 
	 * @param array  $context  Context of the application describing app's name, app's directory and app's main class
	 */
	public function setContext($context) {
		if (empty($this->context)) {
			$this->context = $context;
		}
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
	 * Sets the file that will be used for template compiling
	 * 
	 * @param string $file file that will be used for template compiling
	 */
	public function setResponse($file) {
		// Format the file asked
		if (strpos($file, '/') === false) {
			$file = $this->context['directory'].'templates'.DS.$file.'.html';
		}
		
		$file = str_replace(WITY_PATH, '', $file);
		if (file_exists(WITY_PATH.$file)) {
			// WTemplate automatically adds the base directory defined in WSystem::getTemplate()
			$this->responseFile = $file;
		} else {
			WNote::error('view_set_response', "WView::setResponse(): The response file \"".$file."\" does not exist.", 'plain');
		}
	}
	
	/**
	 * Assigns a variable whose name is $name to a $value
	 * 
	 * @param mixed $name   variable name 
	 * @param mixed $value  variable value
	 */
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
	
	/**
	 * Assigns a list of variables whose names are in $names to their $values
	 * 
	 * @param mixed $names  variable names
	 * @param mixed $values variable values
	 */
	public function assign($names, $values = null) {
		if (is_string($names)) {
			$this->assignOne($names, $values);
		} else if (is_array($names)) {
			foreach ($names as $key => $value) {
				$this->assignOne($key, $value);
			}
		}
	}
	
	/**
	 * Some variables may be considered as "special vars" in a way that they will have a
	 * particular treatment when they will be assigned in the template compilator.
	 * This treatment is defined in this function.
	 * Special vars are not erased. If two different values are assigned to a same special var,
	 * they will stack in an array.
	 * 
	 * For instance, $css and $js are considered as special vars since they will be automaticly
	 * inserted in a <script> or <link> html tag.
	 * $this->assign('css', 'style.css');
	 * {$css} will be replaced by <link href="THEMES_DIR/style.css" rel="stylesheet" type="text/css" />
	 * 
	 * @param string $stack_name stack name
	 * @return string variable value
	 */
	public function getSpecialVar($stack_name) {
		if (empty($this->vars[$stack_name])) {
			return $this->tpl->getVar($stack_name);
		}
		
		switch ($stack_name) {
			case 'css':
				$css = $this->tpl->getVar('css');
				if (is_array($this->vars['css'])) {
					foreach ($this->vars['css'] as $file) {
						$css .= sprintf(
							'<link href="%s%s" rel="stylesheet" type="text/css" />'."\n", 
							(dirname($file) == '.') ? THEMES_DIR.$this->themeName.DS.'css'.DS : '',
							$file
						);
					}
				}
				return $css;
				break;
			
			case 'js':
				$script = $this->tpl->getVar('js');
				if (is_array($this->vars['js'])) {
					foreach ($this->vars['js'] as $file) {
						$script .= sprintf(
							'<script type="text/javascript" src="%s%s"></script>'."\n", 
							(dirname($file) == '.') ? THEMES_DIR.$this->themeName.DS.'js'.DS : '',
							$file
						);
					}
				}
				return $script;
				break;
			
			default:
				return $this->tpl->getVar($stack_name).$this->vars[$stack_name];
				break;
		}
	}
	
	/**
	 * Renders the view
	 * 
	 * @param  string  $response  Template file to be displayed
	 * @return boolean true if view successfully loaded, false otherwise
	 */
	public function render($response = '') {
		// Check if no previous view has already been rendered
		if (self::$response_sent) {
			// HTML sent => abort
			return false;
		}
		
		// Declare response file if given
		if (!empty($response)) {
			$this->setResponse($response);
		}
		
		// Check theme
		if (empty($this->themeName) && WNote::count('view_theme') == 0) {
			WNote::error('view_theme', "WView::render(): No theme given or it was not found.", 'plain');
			return false;
		}
		// Check response file
		if (empty($this->responseFile) && WNote::count('view_response') == 0) {
			WNote::error('view_response', "WView::render(): No response file given.", 'plain');
			return false;
		}
		
		// Flush the notes waiting for their own view
		if (WNote::displayPlainView()) {
			return false;
		}
		
		if ($this->getTheme() != '_blank') {
			// Define {$include} tpl's var
			$this->tpl->assign('include', $this->responseFile);
			
			$themeMainFile = $this->themeDir.'templates'.DS.'index.html';
			
			// Handle notes
			$notes = WNote::parse(WNote::get('*'));
			$this->tpl->assign('notes', $notes);
		} else {
			$themeMainFile = $this->responseFile;
			
			// Trigger notes debug handler for remaining notes
			echo WNote::parse(WNote::get('*'));
		}
		
		// Treat "special vars"
		foreach ($this->specialVars as $stack) {
			$this->vars[$stack] = $this->getSpecialVar($stack);
		}
		
		// Assign View variables
		$this->tpl->assign($this->vars);
		
		$dir = WRoute::getDir();
		if (empty($dir)) {
			// Direct render
			try {
				$this->tpl->display($themeMainFile);
			} catch (Exception $e) {
				WNote::error('view_tpl_display', $e->getMessage(), 'die');
				return false;
			}
		} else {
			// Absolute links fix
			// If $dir is not the root file, then change links
			try {
				$html = $this->tpl->parse($themeMainFile);
				echo str_replace(
					array('src="/', 'href="/', 'action="/'),
					array('src="'.$dir.'/', 'href="'.$dir.'/', 'action="'.$dir.'/'),
					$html
				);
			} catch (Exception $e) {
				WNote::error('view_tpl_parse', $e->getMessage(), 'die');
				return false;
			}
		}
		
		// Mark the view as rendered
		self::$response_sent = true;
		return true;
	}
}
?>