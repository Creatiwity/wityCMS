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
     *
     * @var boolean State variable telling whether the view was already rendered
     */
	private static $response_sent = false;
	
	/**
	 * 
	 * @var array Context of the application describing app's name, app's directory and app's main class
	 */
	private $context;
	
    /**
     *
     * @var WTemplate Instance of WTemplate
     */
	public $tpl;
	
    /**
     *
     * @var string Theme name to be loaded
     */
	private $themeName = '';
    /**
     *
     * @var string Theme directory
     */
	private $themeDir = '';
	
    /**
     *
     * @var string Template response file to display as output
     */
	private $responseFile = '';
	
    /**
     *
     * @var array Variables with a special treatment like "css" and "js"
     */
	private $specialVars = array('css', 'js');
	
    /**
     *
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
			WNote::error('view_set_theme', "WView::setTheme(): The theme \"".$theme."\" does not exist.", 'custom');
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
			WNote::error('view_set_response', "WView::setResponse(): The response file \"".$file."\" does not exist.", 'custom');
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
     * Assigns a variable block to a set of values
     * 
     * @todo Describe a little bit more and better the assignBlock method and the way to use it
     * @param string    $blockName  block name in the template file
     * @param array     $value      set of values that will be set in the block
     */
	public function assignBlock($blockName, $value) {
		if (!isset($this->vars[$blockName.'_block'])) {
			$this->vars[$blockName.'_block'] = array($value);
		} else {
			$this->vars[$blockName.'_block'][] = $value;
		}
	}
	
    /**
     * Returns a "stack" variable with a particular treatment
     * 
     * @todo Describe a little bit more and better the getStack method and the way to use it
     * @param string $stack_name stack name
     * @return string variable value
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
     * Renders the view
     * 
     * @return boolean true if view successfully loaded, false otherwise
     */
	public function render() {
		// Check if no previous view has already been rendered
		if (self::$response_sent) {
			// HTML sent => abort
			return false;
		}
		
		// Check theme
		if (empty($this->themeName) && WNote::count('view_theme') == 0) {
			WNote::error('view_theme', "WView::render(): No theme given or it was not found.", 'custom');
			return false;
		}
		// Check response file
		if (empty($this->responseFile) && WNote::count('view_response') == 0) {
			WNote::error('view_response', "WView::render(): No response file given.", 'custom');
			return false;
		}
		
		// Handle notes
		$notes = WNote::parse(WNote::get('*'));
		if ($this->getTheme() != '_blank') {
			$this->assign('notes', $notes);
		} else {
			echo $notes;
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
			
			$themeMainFile = $this->themeDir.'templates'.DS.'index.html';
		}
		
		$base = WRoute::getDir();
		if ($base == '/') {
			// Direct render
			try {
				$this->tpl->display($themeMainFile);
			} catch (Exception $e) {
				WNote::error('view_tpl_display', $e->getMessage(), 'custom');
				return false;
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
				WNote::error('view_tpl_parse', $e->getMessage(), 'custom');
				return false;
			}
		}
		
		// Mark the view as rendered
		self::$response_sent = true;
		return true;
	}
}
?>