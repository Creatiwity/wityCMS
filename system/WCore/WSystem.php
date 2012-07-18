<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: WCore/WSystem.php 0001 10-04-2010 Fofif $
 * @desc Classe qui sert de plaque tournante d'accès aux classes du 'core'
 */

class WSystem {
	/**
	 * Instance stockée de session
	 */
	private static $sessionInstance;
	
	/**
	 * Instance stockée de WTemplate
	 */
	private static $templateInstance;
	
	/**
	 * Instance stockée du gestionnaire de la bdd
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
				throw new Exception("Classe PDO introuvable");
			}
			
			try {
				# Bug de PHP5.3 : constante PDO::MYSQL_ATTR_INIT_COMMAND n'existe pas
				self::$dbInstance = new PDO($dsn, $user, $password);
			} catch (PDOException $e) {
				die("Erreur : " . $e->getMessage());
			}
			self::$dbInstance->query("SET NAMES 'utf8'");
			self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		
		return self::$dbInstance;
	}
}

?>
