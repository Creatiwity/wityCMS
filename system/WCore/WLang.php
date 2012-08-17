<<<<<<< HEAD
<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WLang.php 0002 10-04-2010 xpLosIve. $
 * @desc	Gestion des fichiers de langages.
 */

class WLang {
	// Langue à utiliser
	public static $language;
	
	// Array multidimensionnel contenant les configurations de langage.
	protected static $values = array();

	/**
	 * Constructeur
	 *
	 * Initialise la classe
	 */
	private static function __construct() {

		if(empty(self::$values)) {

			// Obtention de la langue de l'utilisateur
			self::$language = (wtConfig::get('user.language')) ? wtConfig::get('user.language') : wtConfig::get('system.language');

			// Ouverture du fichier principal de configuration
			include WT_PATH.DS.'languages' . DS . self::$language . DS . 'common.php';

			// Mise à jour des valeurs de langage
			self::$values = $lang;

			// Effacement de la variable $lang
			unset($lang);

		}

	}

	/**
	 * Pour charger un autre fichier
	 *
	 * @access public
	 * @param  string $name     Nom du fichier à charger (sans extension)
	 */
	public static function loadFile($name) {

		if(file_exists(WT_PATH.DS.'languages' . DS . self::$language . DS . $name . '.php')) {

			include WT_PATH.DS.'languages' . DS . self::$language . DS . $name . '.php';

			// Mise à jour des valeurs de langage.
			self::$values = array_merge(self::$values, $lang);

			// Effacage de la variable $lang
			unset($lang);

		}

	}

	/**
	 * Pour assigner une constante de langage
	 *
	 * @access public
	 * @param  string $name     Nom de la constante
	 * @param  string $value    Valeur de la constante
	 */
	public static function assign($name, $value) {

		if(!empty($name) AND !empty($value)) {

			self::$values[$name] = $value;

		}

	}

	/**
	 * Retourne une valeur de constante
	 *
	 * @access public
	 * @param  string $name     Nom de la valeur
	 */
	public static function get($name) {

		if(!empty(self::$values[$name])) {

			return self::$values[$name];

		}

		return false;

	}

	/**
	 * Alias permettant un accès rapide à la fonction get()
	 *
	 * wtLang::_('LANG_CONST');
	 */
	public static function _($name) {

		self::get($name);

	}

}
=======
<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WLang.php 0001 10-08-2012 xpLosIve.|Fofif $
 * @package Wity
 */

class WLang {
	// Langue à utiliser
	public static $language;
	
	private static $lang_dirs = array();
	
	// Lang values
	private static $values = array();
	
	private static $short_node = false;
	
	public static function init() {
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));
		WTemplateCompiler::registerCompiler('lang_close', array('WLang', 'compile_lang_close'));
	}
	
	public static function compile_lang($args) {
		if (empty($args)) {
			self::$short_node = true;
			return "<?php echo WLang::get('";
		} else {
			return "<?php \$lang = WLang::get('".$args."');\n"
				."if (!empty(\$lang)): echo \$lang;\n"
				."else: ?>";
		}
	}
	
	public static function compile_lang_close() {
		if (self::$short_node) {
			self::$short_node = false;
			return "'); ?>";
		} else {
			return "<?php endif; ?>";
		}
	}
	
	public static function selectLanguage($lang) {
		// todo: check $lang is a correct language
		self::$language = strtolower($lang);
	}
	
	public static function addLanguageDir($dir) {
		if (is_dir($dir)) {
			self::$lang_dirs[] = rtrim($dir, '/');
		}
	}
	
	/**
	 * Constructor
	 */
	/*public function __construct($lang = null) {
		if(empty(self::$values)) {

			// Obtention de la langue de l'utilisateur
			self::$language = (wtConfig::get('user.language')) ? wtConfig::get('user.language') : wtConfig::get('system.language');

			// Ouverture du fichier principal de configuration
			include WT_PATH.DS.'languages' . DS . self::$language . DS . 'common.php';

			// Mise à jour des valeurs de langage
			self::$values = $lang;

			// Effacement de la variable $lang
			unset($lang);

		}

	}*/

	/**
	 * Pour charger un autre fichier
	 *
	 * @access public
	 * @param  string $name     Nom du fichier à charger (sans extension)
	 */
	public static function findLanguageFile($dir) {
		if (file_exists($dir.DS.self::$language.'.xml')) {
			
		}
	}

	/**
	 * Pour assigner une constante de langage
	 *
	 * @access public
	 * @param  string $name     Nom de la constante
	 * @param  string $value    Valeur de la constante
	 */
	public static function assign($name, $value) {
		if (!empty($name) && !empty($value)) {
			self::$values[$name] = $value;
		}
	}

	/**
	 * Retourne une valeur de constante
	 *
	 * @access public
	 * @param  string $name Nom de la valeur
	 */
	public static function get($id) {
		// Try to load lang files
		while (!isset(self::$values[$id]) && !empty(self::$lang_dirs)) {
			$dir = array_shift(self::$lang_dir);
			self::loadLanguageFile($dir);
		}
		
		if (isset(self::$values[$id])) {
			return self::$values[$id];
		}
		return '';
	}

	/**
	 * Alias permettant un accès rapide à la fonction get()
	 * WLang::_('LANG_ID');
	 */
	public static function _($id) {
		return self::get($id);
	}

}
>>>>>>> WTemplate + WLang
?>