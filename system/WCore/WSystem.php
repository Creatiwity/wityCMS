<?php 
/**
 * WSystem.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WSystem keeps the session, template and database instances as singletons
 * 
 * <p>If you need the WSession instance, just call :
 * <code>WSystem::getSession();</code>
 * It's the same thing with WTemplate andWDatabase.
 * </p>
 *
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-28-09-2012
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
			include SYS_DIR.'WCore/WSession.php';
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
			include SYS_DIR.'WTemplate/WTemplate.php';
			try {
				self::$templateInstance = new WTemplate(WITY_PATH, CACHE_DIR.'templates'.DS);
			} catch (Exception $e) {
				WNote::error('system_template_instantiation', $e->getMessage(), 'die');
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
			
			$dsn = 'mysql:dbname='.WConfig::get('database.dbname').';host='.WConfig::get('database.server');
			$user = WConfig::get('database.user');
			$password = WConfig::get('database.pw');
			
			self::$dbInstance = new WDatabase($dsn, $user, $password);
			self::$dbInstance->query("SET NAMES 'utf8'");
			self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		
		return self::$dbInstance;
	}
}

?>