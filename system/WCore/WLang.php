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
 * @version 0.3-06-03-2013
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
			$handle = @fopen($file, 'r');
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$line = trim($line);
					if (substr($line, 0, 5) == '<item') {
						// <item id="lang_id">lang_string</item>
						// Find lang_id
						$delimiter1 = strpos($line, '"');
						$line = substr($line, $delimiter1 + 1);
						$delimiter1 = strpos($line, '"');
						$lang_id = substr($line, 0, $delimiter1);
						
						// Find lang_string
						$delimiter1 = strpos($line, '>');
						$delimiter2 = strrpos($line, '<');
						$lang_string = substr($line, $delimiter1 + 1, $delimiter2 - $delimiter1 - 1);
						
						self::assign($lang_id, $lang_string);
					}
				}
				fclose($handle);
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
			$id = array_shift($data);
			$id = WTemplateParser::replaceNodes($id, create_function('$s', "return '\".'.WTemplateCompiler::parseVar(\$s).'.\"';"));
			// is there some data left in $data?
			if (!empty($data)) {
				$args = '';
				foreach ($data as $var) {
					$var_parsed = WTemplateCompiler::parseVar(trim($var, '{}'));
					if (!empty($var_parsed)) {
						$args .= $var_parsed.', ';
					}
				}
				return '<?php echo WLang::get("'.$id.'", array('.$args.')); ?>';
			} else {
				return '<?php echo WLang::get("'.$id.'"); ?>';
			}
		}
		return '';
	}
}

?>