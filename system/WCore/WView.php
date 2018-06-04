<?php
/**
 * WView.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WView handles application's Views.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WView {
	/**
	 * @var array Context of the application describing app's name, app's directory and app's main class
	 */
	private $context = array();

	/**
	 * @var WTemplate Instance of WTemplate
	 */
	public $tpl;

	/**
	 * @var string Theme name for this view
	 */
	private $theme;

	/**
	 * @var string Template file to be used when the view will be rendered
	 */
	private $templateFile = '';

	/**
	 * @var array Global variables with a special treatment like "css" and "js"
	 */
	private static $global_vars = array(
		'css'     => array(),
		'js'      => array(),
		'require' => array()
	);

	/**
	 * @var array Template variables
	 */
	protected $vars = array();

	/**
	 * @var Final view rendered as a string
	 */
	private $rendered_string;

	/**
	 * @var Counts the number of times a view was rendered for each signature.
	 */
	private $render_counts = array();

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
	 * Returns the application context
	 *
	 * @return array Application's context
	 */
	public function getContext($field = '') {
		if (!empty($field)) {
			return (isset($this->context[$field])) ? $this->context[$field] : '';
		}

		return $this->context;
	}

	/**
	 * Updates the signature of the view.
	 *
	 * @param string $value Signature of the view to display in forms
	 */
	public function setSignature($value) {
		if (empty($this->context['signature'])) {
			$this->context['signature'] = $value;
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
	 * Sets the file that will be used for template compiling.
	 * $template needs to include an extension ".html".
	 *
	 * @param string $template File that will be used for template compiling
	 */
	public function setTemplate($template) {
		$file = $template;

		// Use system directory separator
		if (DS != '/') {
			$file = str_replace('/', DS, $file);
		}

		// Format the file asked
		if (strpos($file, DS) === false) {
			$file = $this->getContext('directory').'templates'.DS.$file;
		}

		$route = WRoute::route();
		if (!$route['admin']) {
			$theme_tpl = THEMES_DIR.WConfig::get('config.theme').DS.'templates'.DS.$this->getContext('app').DS.basename($template);

			// Allow template overriding from theme
			if (file_exists($theme_tpl)) {
				$file = $theme_tpl;
			}
		}

		$file = str_replace(WITY_PATH, '', $file);
		if (file_exists(WITY_PATH.$file)) {
			// WTemplate automatically adds the base directory defined in WSystem::getTemplate()
			$this->templateFile = $file;
		} else {
			WNote::error('view_set_template', "WView::setTemplate(): The template file \"".$file."\" does not exist.");
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
	 * Assign values relatively to a default model.
	 *
	 * <code>
	 *   $this->assignRelative(array(
	 *     'var1' => 'default_value1',
	 *     'var2' => 'default_value2'
	 *   ), array(
	 *     'var1' => 'final_value1'
	 *   )); // Will assign var1 = final_value1 and var2 = default_value2
	 * </code>
	 *
	 * @param array $model Model + default values
	 * @param array $values Values to use
	 */
	public function assignDefault(array $model, array $values) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($values[$item]) ? $values[$item] : $default);
		}
	}

	/**
	 * Assigns a variable whose name is $name to a $value
	 *
	 * @param mixed $name   variable name
	 * @param mixed $value  variable value
	 */
	public function assignOne($name, $value) {
		// Is $name a Global var?
		if (isset(self::$global_vars[$name])) {
			if (!in_array($value, self::$global_vars[$name])) {
				self::$global_vars[$name][] = $value;
			}
		} else { // Normal var
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
	 * Some variables may be considered as global vars in a way that they will have a
	 * particular treatment when they will be assigned in the template compilator.
	 * This treatment is defined in this function.
	 * Global vars are not erased. If two different values are assigned to a same global var,
	 * they will stack in an array.
	 *
	 * For instance, $css and $js are considered as global vars since they will be automaticly
	 * inserted in a <script> or <link> html tag.
	 * $this->assign('css', 'style.css');
	 * {$css} will be replaced by <link href="THEMES_DIR/style.css" rel="stylesheet" type="text/css" />
	 *
	 * @param string $stack_name stack name
	 * @return string variable value
	 */
	public function getGlobalVar($stack_name) {
		if (empty(self::$global_vars[$stack_name]) && $stack_name != 'require') {
			return '';
		}

		switch ($stack_name) {
			case 'css':
				$css = '';
				foreach (self::$global_vars['css'] as $file) {
					if (strpos($file, '?') !== false) {
						$file .= '&amp;v='.WConfig::get('config.version');
					} else {
						$file .= '?v='.WConfig::get('config.version');
					}

					$css .= '<link href="'.$file.'" rel="stylesheet" type="text/css" />'."\n";
				}

				return $css;

			case 'js':
				$script = '';
				foreach (self::$global_vars['js'] as $file) {
					if (strpos($file, '?') !== false) {
						$file .= '&amp;v='.WConfig::get('config.version');
					} else {
						$file .= '?v='.WConfig::get('config.version');
					}

					$script .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";
				}

				return $script;

			case 'require':
				if (!file_exists('libraries/libraries.json')) {
					return '';
				}

				$lang_array = WLang::getLangs(true);

				foreach ($lang_array as $key => $value) {
					$lang_array[$key] = json_encode($value);
				}

				$require = '<script type="text/javascript" src="{$wity_base_url}libraries/requirejs/require.min.js"></script>'."\n"
					.'<script>'."\n"
					.'var wity_base_url = "'.WRoute::getBase().'"'.";\n"
					.'var wity_lang_enabled_langs = ['. implode(',', $lang_array) ."];\n"
					.'var wity_lang_default_id = "'.WLang::getDefaultLangId().'"'.";\n"
					.'require.config('.file_get_contents('libraries/libraries.json').');'."\n";

				// If array not empty
				if (self::$global_vars['require']) {
					$require .= 'require(["'.implode('", "', self::$global_vars['require']).'"]);'."\n";
				}

				$require .= '</script>'."\n";

				return $require;

			default:
				return self::$global_vars[$stack_name];
		}
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
	 * @return string The rendered string of the view
	 */
	public function render() {
		$signature = $this->getContext('signature');

		// Check template file
		if (empty($this->templateFile)) {
			return '';
		}

		// Prevent Views from self inclusion more than 5 times
		if (!isset($this->render_counts[$signature])) {
			$this->render_counts[$signature] = 1;
		} else {
			if ($this->render_counts[$signature] >= 5) {
				return WNote::getView(array(WNote::error('WView::render', 'The view of this application may contain a problem: it tried to include itself more than 5 times.')))->render();
			}

			$this->render_counts[$signature]++;
		}

		// Treat global vars
		foreach (self::$global_vars as $stack => $values) {
			$data = $this->getGlobalVar($stack);

			if (!empty($data)) {
				$this->tpl->assign($stack, $data, true);
			}
		}

		// Switch context in WTemplate
		$this->tpl->pushContext();

		// Assign View variables
		$this->tpl->assign($this->vars);

		// Render the view
		$file = $this->getTemplate();
		$this->rendered_string = $this->tpl->parse($file);

		// Come back to previous context
		$this->tpl->popContext();

		// Clean the view for the next render
		$this->templateFile = '';
		$this->context['signature'] = '';
		$this->render_counts[$signature] = 1;

		return $this->rendered_string;
	}

	/**
	 * Retrieves the last rendered view string
	 */
	public function getRenderedString() {
		return $this->rendered_string;
	}
}

?>
