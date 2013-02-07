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
		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));
		
		// Default lang
		$lang_config = WConfig::get('config.lang');
		WLang::selectLang($lang_config);
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
	 * Declares a directory in which there are language files
	 * 
	 * @param string $dir language directory
	 * @return boolean true if $dir is a directory, false otherwise
	 */
	public static function declareLangDir($dir) {
		if (is_dir($dir)) {
			// Save lang directory
			self::$lang_dirs[] = rtrim($dir, DS).DS;
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
			
			// Mark as loaded
			self::$lang_dirs_loaded[] = $dir;
		}
	}
	
	/**
	 * Returns the value in the current language associated to the $name key
	 *
	 * @param  string $name name as it is in the lang file
	 * @return string value as it is after compiling the lang file
	 */
	public static function get($name, $params = null) {
		if (!empty($name)) {
			// Try to load lang files
			while (!isset(self::$values[$name]) && !empty(self::$lang_dirs)) {
				self::loadLangFile(array_shift(self::$lang_dirs));
			}
			
			if (isset(self::$values[$name])) {
				if (!empty($params)) {
					if (!is_array($params)) {
						$params = array(self::$values[$name], $params);
					} else {
						array_unshift($params, self::$values[$name]);
					}
					return call_user_func_array('sprintf', $params);
				} else {
					return self::$values[$name];
				}
			}
		}
		return $name;
	}
	
	/**
	 * get($name) alias
	 * 
	 * Example : <code>WLang::_('LANG_ID');</code>
	 * 
	 * @param string $name name as it is in the lang file
	 * @return string value as it is after compiling the lang file
	 */
	public static function _($name, $params = null) {
		return self::get($name, $params);
	}
	
	/*****************************************
	 * WTemplateCompiler's new handlers part *
	 *****************************************/
	/**
	 * Handles the {lang} node in WTemplate
	 * {lang} gives access to translation variables
	 * sprintf format (such as %s) may be use in language files like this :
	 * {lang index|{$arg1}} = sprintf(WLang::_('index'), {$arg1})
	 * 
	 * @param string $args language identifier if no closing node in template file
	 * @return string php string that calls the WLang::get()
	 */
	public static function compile_lang($args) {
		if (!empty($args)) {
			$data = explode('|', $args);
			$id = trim(array_shift($data));
			if (!empty($data)) {
				$args = '';
				foreach ($data as $var) {
					$var_parsed = WTemplateCompiler::parseVar(trim($var, '{}'));
					if (!empty($var_parsed)) {
						$args .= $var_parsed.', ';
					}
				}
				return "<?php echo WLang::get('".$id."', array(".$args.")); ?>";
			} else {
				return "<?php echo WLang::get('".$id."'); ?>";
			}
		}
		return '';
	}
}

?>