<?php
/**
 * WSystem.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WSystem keeps the session, template and database instances as singletons.
 *
 * If you need the WSession instance, just call :
 * <code>WSystem::getSession();</code>
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WSystem {
	/**
	 * @var WSession Session object
	 */
	private static $sessionInstance;

	/**
	 * @var WTemplate WTemplate object
	 */
	private static $templateInstance;

	/**
	 * @var WDatabase WDatabase object
	 */
	private static $dbInstance;

	/**
	 * Returns current session or creates it if it doesn't exist yet
	 * @return WSession returns current session
	 */
	public static function getSession() {
		if (!is_object(self::$sessionInstance)) {
			self::$sessionInstance = new WSession();
		}

		return self::$sessionInstance;
	}

	/**
	 * Returns current template or creates it if it doesn't exist yet
	 * @return WSession returns current template
	 */
	public static function getTemplate() {
		if (!is_object(self::$templateInstance)) {
			try {
				$cache_dir = CACHE_DIR.'templates'.DS;
				self::$templateInstance = new WTemplate(WITY_PATH, $cache_dir);

				// Checks the compile directory in Debug mode
				if (self::$templateInstance->getCompileDir() == '') {
					WNote::info('cache_template_failed', WLang::get('error_cache_template_failed', $cache_dir), 'debug');
				}
			} catch (Exception $e) {
				WNote::error('template_init_error', $e->getMessage(), 'die');
			}
		}

		return self::$templateInstance;
	}

	/**
	 * Returns current database manager or creates it if it doesn't exist yet
	 * @return WSession returns current database manager
	 */
	public static function getDB() {
		if (!is_object(self::$dbInstance)) {
			WConfig::load('database', SYS_DIR.'config'.DS.'database.php', 'php');

			$server = WConfig::get('database.server');
			$dbname = WConfig::get('database.dbname');
			$port   = WConfig::get('database.port');
			$dsn    = 'mysql:dbname='.$dbname.';host='.$server;

			if (!empty($port)) {
				$dsn .= ';port='.$port;
			}

			$user     = WConfig::get('database.user');
			$password = WConfig::get('database.pw');

			if (empty($server) || empty($dbname) || empty($user)) {
				WNote::error('system_database_init', WLang::get('error_database_bad_credentials'), 'die');
			}

			self::$dbInstance = new WDatabase($dsn, $user, $password);
			self::$dbInstance->query("SET NAMES 'utf8'");
			self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}

		return self::$dbInstance;
	}
}

?>
