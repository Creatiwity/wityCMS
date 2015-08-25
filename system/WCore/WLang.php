<?php 
/**
 * WLang.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WLang manages everything about languages.
 * 
 * Language values are defined within application in XML files.
 * This class will calculate the most suitable language for the current user
 * and load the corresponding language files.
 * 
 * @package System\WCore
 * @author xpLosIve
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.5.0-dev-06-03-2013
 */
class WLang {
	/**
	 * @var array Languages to use in order of priority
	 */
	private static $lang = '';
	
	/**
	 * @var array List of language directories registered
	 */
	private static $lang_dirs = array();
	
	/**
	 * @var array Language values associated to their constant
	 */
	private static $values = array();
	
	/**
	 * Initializes the Lang template compiler and the language of the user.
	 */
	public static function init() {
		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));

		// Add config lang
		self::setLang(WConfig::get('config.lang'));
		
		// Configure locale
		setlocale(LC_ALL, WConfig::get('config.lang'));
	}
	
	/**
	 * Parses the languages received from the browser through header Accept-Language.
	 * See your browser language configuration to manage your priority list.
	 * 
	 * @return array List of languages prioritized
	 */
	public static function getBrowserLang() {
		if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return array();
		}
		
		// Exemple of $http_lang: 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4'
		// q-value is a ponderation: nothing or 1 means favorite lang, 0 means to avoid
		$http_lang = trim($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$priority_lang = array();
		
		if (!empty($http_lang)) {
			$accept_lang = explode(',', $http_lang);
			
			foreach ($accept_lang as $lang_and_q) {
				$detail = explode(';', $lang_and_q);
				$lang = strtolower(substr($detail[0], 0, 2));
				
				if (strlen($lang) == 2) { // Lang must contain 2 chars (ex: 'en')
					// Find q-value
					if (sizeof($detail) == 1) {
						$q_value = 1;
					} else {
						$q_value = floatval(substr($detail[1], 2, 4));
					}
					
					// Updates the $priority_lang array
					if ($q_value > 0) {
						if (array_key_exists($lang, $priority_lang)) {
							// Lang already found but q is higher
							if ($q_value > $priority_lang[$lang]) {
								$priority_lang[$lang] = $q_value;
							}
						} else {
							$priority_lang[$lang] = $q_value;
						}
					}
				}
			}
		}
		
		// q-values sorting and final extraction of the ordered keys
		arsort($priority_lang);
		return array_keys($priority_lang);
	}
	
	/**
	 * Sets the language to load for the current session.
	 * 
	 * @param string $lang Language name (2 letters identifier)
	 */
	public static function setLang($lang) {
		self::$lang = strtolower(substr($lang, 0, 2));
	}
	
	/**
	 * Returns curent language of the user.
	 * 
	 * @return string
	 */
	public static function getLang() {
		return self::$lang;
	}
	
	public static function getLangID() {
		$lang = self::getLang();
		
		$db = WSystem::getDB();

		$pre = $db->prepare('SELECT id FROM languages WHERE iso = ?');
		$pre->execute(array(strtoupper($lang)));
		return $pre->fetch(PDO::FETCH_COLUMN);
	}

	/**
	 * Returns list of lang IDs
	 * 
	 * @return array
	 */
	public static function getLangIDS() {
		$db = WSystem::getDB();

		$que = $db->query('SELECT id FROM languages');
		return $que->fetchAll(PDO::FETCH_COLUMN);
	}
	
	/**
	 * Assigns a new language constant.
	 * If the constant was already defined, it will keep the previous value by default.
	 *
	 * @param string $name  name as it is in the lang file
	 * @param string $value value as it is after compiling the lang file
	 * @param bool $overwrite Forces the reassignement of a new value.
	 */
	public static function assign($name, $value, $overwrite = false) {
		if ((!isset(self::$values[$name]) || $overwrite) && !empty($name) && !empty($value)) {
			self::$values[$name] = $value;
		}
	}
	
	/**
	 * Declares a directory containing language files.
	 * 
	 * @param string $dir language directory
	 * @return boolean true if $dir is a directory, false otherwise
	 */
	public static function declareLangDir($dir) {
		if (is_dir($dir)) {
			$dir = rtrim($dir, DS).DS;
			$files = glob($dir.'*');
			
			if (!empty($files)) {
				$lang_files = array();
				
				// Find all files of this dir
				foreach ($files as $file) {
					$lang = substr(basename($file), 0, 2);
					$lang_files[$lang] = $file;
				}
				
				self::$lang_dirs[str_replace(WITY_PATH, '', $dir)] = $lang_files;
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Loads a language file.
	 * 
	 * @param string $dir language file path without its extension and without the locale identifier
	 */
	private static function loadLangFile($file) {
		// Checks that file exists and not already loaded
		if (file_exists($file)) {
			// Parses XML file
			$string = file_get_contents($file);
			$xml = new SimpleXMLElement($string);
			foreach ($xml->item as $lang_item) {
				$lang_string = dom_import_simplexml($lang_item)->nodeValue;
				self::assign((string) $lang_item->attributes()->id, $lang_string);
			}
		}
	}
	
	/**
	 * Returns the value in the current language associated to the $name key.
	 *
	 * @param  string $name name as it is in the lang file
	 * @return string value as it is after compiling the lang file
	 */
	public static function get($name, $params = null) {
		$name = trim($name);
		
		if (empty($name)) {
			return '';
		}
		
		// Load the lang value if not already set
		foreach (self::$lang_dirs as $dir_name => $dir) {
			if (isset($dir[self::$lang])) {
				self::loadLangFile($dir[self::$lang]);
			}

			// Remove the directory treated
			unset(self::$lang_dirs[$dir_name]);
		}
		
		if (isset(self::$values[$name])) {
			// Replace given parameters in the lang string
			if (!is_null($params)) {
				if (strpos(self::$values[$name], '%s') !== false) {
					$args = func_get_args();
					$args[0] = self::$values[$name];
					
					return call_user_func_array('sprintf', $args);
				} else if (is_array($params)) {
					$string = self::$values[$name];
					foreach ($params as $key => $value) {
						$string = str_replace('{{'.$key.'}}', $value, $string);
					}
					
					return $string;
				}
			}
			
			return self::$values[$name];
		} else {
			return ucfirst(str_replace('_', ' ', $name));
		}
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
	 * @param string $args language identifier
	 * @return string php string that calls the WLang::get()
	 */
	public static function compile_lang($args) {
		if (!empty($args)) {
			// Replace the template variables in the string
			$args = WTemplateParser::replaceNodes($args, create_function('$s', "return '\".'.WTemplateCompiler::parseVar(\$s).'.\"';"));
			
			$data = explode('|', $args);
			$id = array_shift($data);
			
			if (strlen($id) > 0) {
				// Find parameters to replace the %s in the lang string
				$params = '';
				if (!empty($data)) {
					$args = '';
					foreach ($data as $var) {
						$args .= '"'.$var.'", ';
					}
					
					$params = ', '.substr($args, 0, -2);
				}
				
				// Build final php lang string
				return '<?php echo WLang::get("'.$id.'"'.$params.'); ?>';
			}
		}
		
		return '';
	}
}

?>
