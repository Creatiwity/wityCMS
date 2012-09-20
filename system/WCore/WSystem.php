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
			self::$templateInstance = new WTemplate(WITY_PATH, CACHE_DIR.'templates'.DS);
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
			
			if (!class_exists('PDO')) {
				throw new Exception("WSystem::getDB(): Class PDO unfound.");
			}
			
			try {
				# Bug de PHP5.3 : constante PDO::MYSQL_ATTR_INIT_COMMAND n'existe pas
				@self::$dbInstance = new PDO($dsn, $user, $password);
			} catch (PDOException $e) {
				WNote::display_full(array(WNote::error('sql_conn_error', "Impossible to connect to MySQL.<br /><br /><em>PDO's error</em><br />".utf8_encode($e->getMessage()), 'ignore')));
				die;
			}
			self::$dbInstance->query("SET NAMES 'utf8'");
			self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		
		return self::$dbInstance;
	}
}

?>