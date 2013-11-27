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
 * @version 0.4.0-28-09-2012
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
			require_once SYS_DIR.'WCore/WSession.php';
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
			require_once SYS_DIR.'WTemplate/WTemplate.php';
			try {
				self::$templateInstance = new WTemplate(WITY_PATH, CACHE_DIR.'templates'.DS);
				
				// Checks the compile directory in Debug mode
				if (self::$templateInstance->getCompileDir() == '' && WConfig::get('config.debug')) {
					WNote::info('system_template_init', "Impossible to create cache directory in ".CACHE_DIR.'templates'.DS.".");
				}
			} catch (Exception $e) {
				WNote::error('system_template_init', $e->getMessage(), 'die');
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
			// Chargement des infos db
			WConfig::load('database', SYS_DIR.'config'.DS.'database.php', 'php');
			
			$server = WConfig::get('database.server');
			$dbname = WConfig::get('database.dbname');
			$dsn = 'mysql:dbname='.$dbname.';host='.$server;
			$user = WConfig::get('database.user');
			$password = WConfig::get('database.pw');
			
			if (empty($server) || empty($dbname) || empty($user)) {
				WNote::error('system_database_init', "Information is missing to connect to the database: please, check the server, database name or the user name in system/config/database.php.", 'die');
			}
			
			self::$dbInstance = new WDatabase($dsn, $user, $password);
			self::$dbInstance->query("SET NAMES 'utf8'");
			self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		
		return self::$dbInstance;
	}
}

?>
