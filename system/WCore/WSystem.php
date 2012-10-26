<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WSystem.php 0001 10-04-2010 Fofif $
 * @package Wity
 */

class WSystem {
	/**
	 * Session object
	 */
	private static $sessionInstance;
	
	/**
	 * WTemplate object
	 */
	private static $templateInstance;
	
	/**
	 * DB Manager object
	 */
	private static $dbInstance;
	
	public static function getSession() {
		if (!is_object(self::$sessionInstance)) {
			include SYS_DIR.'WCore/WSession.php';
			self::$sessionInstance = new WSession();
		}
		
		return self::$sessionInstance;
	}
	
	public static function getTemplate() {
		if (!is_object(self::$templateInstance)) {
			include SYS_DIR.'WTemplate/WTemplate.php';
			try {
				self::$templateInstance = new WTemplate(WITY_PATH, CACHE_DIR.'templates'.DS);
			} catch (Exception $e) {
				WNote::error('system_template_instanciation', $e->getMessage(), 'die');
			}
		}
		
		return self::$templateInstance;
	}
	
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