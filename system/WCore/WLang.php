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
	private static $lang_dirs_loaded = array();
	
	// Lang values
	private static $values = array();
	
	/**
	 * Declaration of new compiler's handlers
	 */
	public static function init() {
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('lang', array('WLang', 'compile_lang'));
		WTemplateCompiler::registerCompiler('lang_close', array('WLang', 'compile_lang_close'));
	}
	
	public static function selectLang($lang) {
		// todo: check if $lang is a correct language
		self::$language = strtolower(substr($lang, 0, 2));
	}

	/**
	 * Assign a new language constant
	 *
	 * @access public
	 * @param string $name  Name
	 * @param string $value Value
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
			$dir = array_shift(self::$lang_dirs);
			self::loadLangFile($dir);
			// Mark as loaded
			self::$lang_dirs_loaded[] = $dir;
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
	
	/**
	 * Pour charger un autre fichier
	 *
	 * @access public
	 * @param  string $name     Nom du fichier à charger (sans extension)
	 */
	public static function declareLangDir($dir) {
		if (is_dir($dir)) {
			// Save lang directory
			self::$lang_dirs[] = rtrim($dir, '/').'/';
			return true;
		}
		return false;
	}
	
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
	 * Variable to remember if a node was opened
	 */
	private static $short_node = false;
	
	/**
	 * Handles the {lang} opening node in WTemplate
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