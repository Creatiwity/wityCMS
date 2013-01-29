<?php 
/**
 * WLang.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WLang manages everything about languages
 *
 * @package System\WCore
 * @author xpLosIve
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-28-09-2012
 */
class WLang {

	/**
	 * @var string current language
	 */
	public static $language;
	
	/**
	 * @var array(string) list of all language directories 
	 */
	private static $lang_dirs = array();
	
	/**
	 * @var array(string) list of all loaded language directories 
	 */
	private static $lang_dirs_loaded = array();

	/**
	 * @var array(string) list of all key (name)=>value (in current language) pairs 
	 */
	private static $values = array();
	
	/**
	 * Declaration of new compiler's handlers
	 */
	public static function init() {
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));
		WTemplateCompiler::registerCompiler('lang_close', array('WLang', 'compile_lang_close'));
	}
	
	/**
	 * Stores the choosen language $lang in the private property $language
	 * 
	 * @param string $lang choosen language
	 */
	public static function selectLang($lang) {
		// todo: check if $lang is a correct language
		self::$language = strtolower(substr($lang, 0, 2));
	}
	
	/**
	 * Assign a new language constant
	 *
	 * @param string $name  name as it is in the lang file
	 * @param string $value value as it is after compiling the lang file
	 */
	public static function assign($name, $value) {
		if (!empty($name) && !empty($value)) {
			self::$values[$name] = $value;
		}
	}
	
	/**
	 * Returns the value in the current language associated to the $name key
	 *
	 * @param  string $name name as it is in the lang file
	 * @return string value as it is after compiling the lang file
	 */
	public static function get($name) {
		// Try to load lang files
		while (!isset(self::$values[$name]) && !empty(self::$lang_dirs)) {
			$dir = array_shift(self::$lang_dirs);
			self::loadLangFile($dir);
			// Mark as loaded
			self::$lang_dirs_loaded[] = $dir;
		}
		
		if (isset(self::$values[$name])) {
			return self::$values[$name];
		}
		return $name;
	}
	
	/**
	 * get($name) alias
	 * 
	 * Example : <code>WLang::_('LANG_ID');</code>
	 * 
	 * @param string $name name as it is in the template file
	 * @return string value as it is after compiling the template file
	 */
	public static function _($name) {
		return self::get($name);
	}
	
	/**
	 * Declares a directory in which there are language files
	 * 
	 * @param string $dir language directory
	 * @return boolean true if $dir is a directory, false otherwise
	 */
	public static function declareLangDir($dir) {
		if (is_dir($dir)) {
			// Save lang directory
			self::$lang_dirs[] = rtrim($dir, '/').'/';
			return true;
		}
		return false;
	}
	
	/**
	 * Loads a language file
	 * 
	 * @param string $dir language file path without its extension and without the locale identifier
	 */
	private static function loadLangFile($dir) {
		$file = $dir.self::$language.'.xml';
		if (file_exists($file)) {
			$string = file_get_contents($file);
			$xml = new SimpleXMLElement($string);
			
			foreach ($xml->item as $lang_item) {
				self::assign((string) $lang_item->attributes()->id, (string) $lang_item);
			}
		}
	}
	
	/*****************************************
	 * WTemplateCompiler's new handlers part *
	 *****************************************/
	
	/**
	 *
	 * @var boolean true if a node is open 
	 */
	private static $short_node = false;
	
	/**
	 * Handles the {lang} opening node in WTemplate
	 * 
	 * @todo Remove syntax choice
	 * @param string $args optional language identifier if no closing node in template file
	 * @return string php string that calls the WLang::get()
	 */
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
	
	/**
	 * Handles the {/lang} closing node in WTemplate
	 * 
	 * @return string php string that closes the 
	 */
	public static function compile_lang_close() {
		if (self::$short_node) {
			self::$short_node = false;
			return "'); ?>";
		} else {
			return "<?php endif; ?>";
		}
	}
}

?>