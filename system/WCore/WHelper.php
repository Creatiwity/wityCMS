<?php 
/**
 * WHelper.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WHelper automatically instantiates for you small libraries.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WHelper {
	/**
	 * @var array Stores the class names and params expected of the helpers already loaded
	 */
	private static $helpers_loaded = array();

	/**
	 * Obtains a helper's instance
	 *
	 * @param $helper_name  Name of the helper
	 * @param $params       Params to give to the __construct() method of the helper
	 * @throws Exception
	 * @return Object
	 */
	public static function load($helper_name, array $params = array()) {
		// Calculate normalized helper name
		$helper_dir = HELPERS_DIR.$helper_name.DS;

		// Check helper directory existency
		if (!file_exists($helper_dir.'helper.json')) {
			throw new Exception("Helper ".$helper_name." cannot be found in ".HELPERS_DIR);
		}

		// Check whether helper has already been included
		if (!isset(self::$helpers_loaded[$helper_name])) {
			// Load helper loading scheme
			$helper = json_decode(file_get_contents($helper_dir.'helper.json'), true);

			// Create a new line in the table
			self::$helpers_loaded[$helper_name] = array(
				'class' => $helper['class'],
				'params_expected' => count($helper['params'])
			);

			// Include helper main file containing the class to instantiate
			include_once HELPERS_DIR.$helper_name.DS.$helper['file'];

			// Check helper class existency
			if (!class_exists($helper['class'])) {
				throw new Exception("Helper \"".$helper_name."\" misconfiguration: class ".$helper['class']." does not exist in file \"".$helper['file']);
			}
		}

		// Check params number
		$params_given = count($params);
		if ($params_given < self::$helpers_loaded[$helper_name]['params_expected']) {
			throw new Exception("Missing arguments to instantiate helper \"".$helper_name."\": "
				.$params_given." given / "
				.self::$helpers_loaded[$helper_name]['params_expected']." expected.");
		}

		// Return helper instance
		if ($params_given > 0) {
			$reflection_class = new ReflectionClass(self::$helpers_loaded[$helper_name]['class']);
			return $reflection_class->newInstanceArgs($params);
		} else {
			return new self::$helpers_loaded[$helper_name]['class']();
		}
	}
}

?>
