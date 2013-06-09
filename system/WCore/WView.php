<?php 
/**
 * WView.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WView handles application's Views
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-17-01-2013
 */
class WView {
	/**
	 * @var array Context of the application describing app's name, app's directory and app's main class
	 */
	private $context;
	
	/**
	 * @var WTemplate Instance of WTemplate
	 */
	public $tpl;
	
	/**
	 * @var array List of headers for this view
	 */
	private $headers = array();
	
	/**
	 * @var string Theme name for this view
	 */
	private $theme;
	
	/**
	 * @var string Template file to be used when the view will be rendered
	 */
	private $templateFile = '';
	
	/**
	 * @var array Variables with a special treatment like "css" and "js"
	 */
	private $specialVars = array('css', 'js');
	
	/**
	 * @var array Template variables
	 */
	protected $vars = array();
	
	/**
	 * @var Final view rendered as a string
	 */
	private $rendered_string;
	
	/**
	 * Setup template
	 */
	public function __construct() {
		$this->tpl = WSystem::getTemplate();
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
	 * Gets the name of the current view
	 * 
	 * @return string View name
	 */
	public function getName() {
		if (isset($this->context['name'])) {
			return $this->context['name'];
		} else {
			return get_class($this);
		}
	}
	
	/**
	 * Sets the file that will be used for template compiling
	 * 
	 * @param string $file file that will be used for template compiling
	 */
	public function setTemplate($file) {
		// Format the file asked
		if (strpos($file, '/') === false) {
			$file = $this->context['directory'].'templates'.DS.$file.'.html';
		}
		
		$file = str_replace(WITY_PATH, '', $file);
		if (file_exists(WITY_PATH.$file)) {
			// WTemplate automatically adds the base directory defined in WSystem::getTemplate()
			$this->templateFile = $file;
		} else {
			WNote::error('view_set_template', "WView::setTemplate(): The template file \"".$file."\" does not exist.", 'plain');
		}
	}
	
	/**
	 * Returns the template file configured for the current view
	 * 
	 * @return string Template file href
	 */
	public function getTemplate() {
		return $this->templateFile;
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
	 * Get the value of one assigned variable
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getVar($name) {
		if (isset($this->vars[$name])) {
			return $this->vars[$name];
		}
		return null;
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
			
			default:
				return $this->tpl->getVar($stack_name).$this->vars[$stack_name];
		}
	}
	
	/**
	 * Set a new header for the response
	 * Will be assigned in WResponse::render()
	 * 
	 * @param string $name Header's name
	 * @param string $value
	 */
	public function setHeader($name, $value) {
		$this->headers[strtolower($name)] = $value;
	}
	
	/**
	 * Get the headers for this view
	 * 
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	 * Define the theme for this view
	 * 
	 * @param string $theme
	 */
	public function setTheme($theme) {
		$this->theme = $theme;
	}
	
	/**
	 * Get the theme name for this view
	 * 
	 * @return string Theme name
	 */
	public function getTheme() {
		return $this->theme;
	}
	
	/**
	 * Renders the view
	 * 
	 * @param  string $action Name of the action in the view to display
	 * @return string The rendered string of the view
	 */
	public function render($action = '', $model = array()) {
		if (!empty($this->rendered_string)) {
			return $this->rendered_string;
		}
		
		// If model is a Note, return it parsed
		if (array_keys($model) == array('level', 'code', 'message','handlers')) {
			return $this->rendered_string = WNote::parse(array($model));
		}
		
		if (!empty($action)) {
			// Prepare the view
			if (method_exists($this, $action)) {
				$this->$action($model);
			}
			
			// Declare template file if given
			if ($this->getTemplate() == "") {
				$this->setTemplate($action);
			}
		}
		
		// Check template file
		if (empty($this->templateFile)) {
			WNote::error('view_template', "WView::prepare(): No template file found for the view ".$this->getName()."/".$action.".", 'plain');
			return false;
		}
		
		// Treat "special vars"
		foreach ($this->specialVars as $stack) {
			$this->vars[$stack] = $this->getSpecialVar($stack);
		}
		
		// Assign View variables
		$this->tpl->assign($this->vars);
		
		return $this->rendered_string = $this->tpl->parse($this->getTemplate());
	}
}
?>