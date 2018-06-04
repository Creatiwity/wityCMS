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
 * @version 0.6.2-04-06-2018
 */
class WLang {
	/**
	 * @var array Current language - ex: 'en_EN'
	 */
	private static $lang_code = '';

	/**
	 * @var array Current language ISO format - ex: 'en'
	 */
	private static $lang_iso = '';

	/**
	 * @var array List of registered directories containing language files
	 */
	private static $lang_dirs = array();

	/**
	 * @var array Language values associated to their constant
	 */
	private static $values = array();

	/**
	 * @var WDatabase
	 */
	private static $db;

	/**
	 * Initializes the Lang template compiler and the language of the user.
	 */
	public static function init() {
		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));

		// Init database
		self::$db = WSystem::getDB();
		self::$db->declareTable('languages');

		// Init locale
		self::setLang('en_EN');
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
				$lang = str_replace('-', '_', $detail[0]);

				if (strlen($lang) == 5) { // Lang must contain 5 chars (ex: 'en_EN')
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
	 * Sets the current lang.
	 *
	 * @param string $lang Language code (ex: 'en_EN')
	 * @return bool
	 */
	public static function setLang($lang_code) {
		if ($lang_code == self::$lang_code) {
			return true;
		}

		self::$lang_code = $lang_code;
		self::$lang_iso = strtolower(substr($lang_code, 0, 2));

		// Configure locale
		setlocale(LC_ALL, self::$lang_code);

		// Clean previous values to reload them
		self::$values = array();

		return true;
	}

	/**
	 * Sets the current lang by Id.
	 *
	 * @param int $id_lang
	 * @return bool
	 */
	public static function setLangWithId($id_lang) {
		$lang = self::getLangWithId($id_lang);

		if (!empty($lang['code'])) {
			return self::setLang($lang['code']);
		}

		return false;
	}

	/**
	 * Returns the current lang.
	 *
	 * @return string
	 */
	public static function getLang() {
		return self::$lang_code;
	}

	/**
	 * Returns the ISO code of the current lang.
	 *
	 * @return string
	 */
	public static function getLangISO() {
		return self::$lang_iso;
	}

	/**
	 * Returns the Id of current lang.
	 *
	 * @return int
	 */
	public static function getLangId() {
		$lang_iso = self::getLangISO();

		$pre = self::$db->prepare('SELECT id FROM languages WHERE iso = ?');
		$pre->execute(array($lang_iso));

		return intval($pre->fetch(PDO::FETCH_COLUMN));
	}

	/**
	 * Returns the Id of current lang.
	 *
	 * @return array
	 */
	public static function getLangWithId($id_lang) {
		$pre = self::$db->prepare('SELECT * FROM languages WHERE id = ?');
		$pre->execute(array($id_lang));

		return $pre->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Returns the Id of the default lang.
	 *
	 * @return int
	 */
	public static function getDefaultLangId() {
		if (!empty(self::$db)) {
			$query = self::$db->query('SELECT id FROM languages WHERE is_default = 1');

			return intval($query->fetch(PDO::FETCH_COLUMN));
		}

		return 0;
	}

	/**
	 * Returns the default lang data.
	 *
	 * @return array
	 */
	public static function getDefaultLang() {
		$pre = self::$db->prepare('SELECT * FROM languages WHERE is_default = 1');
		$pre->execute();

		return $pre->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Returns langs data.
	 *
	 * @param  bool Only return enabled languages?
	 * @return array
	 */
	public static function getLangs($enabled = true) {
		$cond = '';
		if ($enabled) {
			$cond = ' WHERE enabled = 1';
		}

		$langs = array();

		if (!empty(self::$db)) {
			$query = self::$db->query('SELECT * FROM languages'.$cond.' ORDER BY is_default DESC');

			while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
				$langs[intval($data['id'])] = $data;
			}
		}

		return $langs;
	}

	/**
	 * Returns list of lang Ids
	 *
	 * @param  bool Only return enabled languages?
	 * @return array
	 */
	public static function getLangIds($enabled = true) {
		$cond = '';
		if ($enabled) {
			$cond = ' WHERE enabled = 1';
		}

		$query = self::$db->query('SELECT id FROM languages'.$cond.' ORDER BY is_default DESC');

		return $query->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Load countries list from helpers.
	 *
	 * @return array array(ISO => Country)
	 */
	private static function getRawCountries() {
		static $countries = array();

		if (empty($countries)) {
			include HELPERS_DIR.'countries'.DS.'countries.php';
		}

		return $countries;
	}

	/**
	 * Get display region into the current language.
	 *
	 * @param string $iso_code
	 * @return string Country name
	 */
	public static function getCountry($iso_code) {
		if (function_exists('locale_get_display_region')) {
			return locale_get_display_region('-'.$iso_code, self::getLang());
		} else {
			$countries = self::getRawCountries();

			return !empty($countries[$iso_code]) ? $countries[$iso_code] : $iso_code;
		}
	}

	/**
	 * Get countries list
	 *
	 * @return array
	 */
	public static function getCountries() {
		static $countries = array();

		if (empty($countries)) {
			$countries = self::getRawCountries();

			// Translate countries
			foreach ($countries as $iso_code => $name) {
				$countries[$iso_code] = self::getCountry($iso_code);
			}

			asort($countries);
		}

		return $countries;
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
	 * @param  string $dir language directory
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
					$extension = pathinfo($file, PATHINFO_EXTENSION);

					if ($extension == 'xml') {
						$lang = substr(basename($file), 0, 2);
						$lang_files[$lang] = $file;
					}
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
		if (!isset(self::$values[$name])) {
			foreach (self::$lang_dirs as $dir_name => $dir) {
				if (isset($dir[self::$lang_iso])) {
					self::loadLangFile($dir[self::$lang_iso]);
				}
			}
		}

		if (!isset(self::$values[$name])) {
			self::$values[$name] = $name;
		}

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
