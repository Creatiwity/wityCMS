<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: WCore/WRoute.php 0002 12-02-2012 Fofif $
 */

class WRoute {
	/**
	 * @todo Un systeme sans URLREWRITING http://MonSite.fr/index.php/News/1
	 */
	
	/**
	 * L'URL de la page
	 */
	public static $url;
	
	public static function init() {
		$dir = self::getDir();
		if ($dir != '/') {
			self::$url = str_replace($dir, '', $_SERVER['REQUEST_URI']);
		} else {
			self::$url = $_SERVER['REQUEST_URI'];
		}
		
		// Chargement des valeurs de config du routage
		WConfig::load('route', SYS_DIR.'config'.DS.'route.php', 'php');
	}
	
	public static function route() {
		// Vérification de l'existence d'un routage perso
		$perso = WConfig::get('route.perso');
		$url = trim(self::$url, '/');
		if (isset($perso[$url])) {
			self::setRoute($perso[$url]);
		} else {
			// Chargement de la config URL
			$routage = self::parseURL(self::$url);
			if (!empty($routage)) {
				self::setRoute($routage);
			} else {
				// Si rien n'a été fourni, chargement du routage par défaut
				self::setRoute(WConfig::get('route.default'));
			}
		}
	}
	
	public static function getBase() {
		return rtrim(WConfig::get('config.base'), '/');
	}
	
	/**
	 * Obtention de l'url du dossier où se situe wity
	 */
	public static function getDir() {
		return substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
	}
	
	/**
	 * Retourne l'url de la page
	 */
	public static function getURL() {
		return self::$url;
	}
	
	/**
	 * Obtention du référant (adresse précédente)
	 */
	public static function getReferer() {
		$base = self::getBase();
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $base) !== false) {
			return $_SERVER['HTTP_REFERER'];
		} else {
			return $base;
		}
	}
	
	/**
	 * 
	 * <code>
	 *   WRoute::defineRoutePerso('/test/', array(
	 *     'appName',
	 *     array('arg1', 'arg2')
	 *   ));
	 * </code>
	 */
	public static function defineRoutePerso($uri, array $routage) {
		// Vérification de la structure
		if (self::checkRouteStructure($routage)) {
			$perso = WConfig::get('route.perso');
			$perso[$uri] = $routage;
			WConfig::set('route.perso', $perso);
			WConfig::save('route');
			return true;
		}
		return false;
	}
	
	/**
	 * Suppression d'un routage perso
	 */
	public static function deleteRoutePerso($uri) {
		if (!is_null(WConfig::get('route.perso.'.$uri))) {
			$perso = WConfig::get('route.perso');
			unset($perso[$uri]);
			WConfig::set('route.perso', $perso);
			WConfig::save('route');
		}
	}
	
	/**
	 * @desc Pour les url du type http://MonSite.fr/News/Read/1
	 */
	private static function parseURL($url) {
		$routage = array();
		
		// Nettoyage
		$url = trim($url, '/');
		$url = str_replace(array('index.php', '.html', '.htm'), '', $url);
		$url = preg_replace('#\?.*$#', '', $url); // Nettoyage des query string
		
		$array = explode('/', $url);
		// Nom de l'appli fourni
		if (!empty($array[0])) {
			$routage[] = strtolower(array_shift($array));
			if (sizeof($array) > 0) {
				// Stockage des arguments
				$routage[] = $array;
			} else {
				$routage[] = array();
			}
		}
		return $routage;
	}
	
	/**
	 * Vérifie qu'un routage a la bonne structure qui doit être :
	 * $routage = array('AppName', array('argument1', 'argument2'));
	 * @param mixed
	 * @return bool
	 */
	private static function checkRouteStructure(array $routage) {
		if (sizeof($routage) == 2) {
			if (is_string($routage[0])) {
				if (is_array($routage[1])) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Définie les valeurs de routage dans la configuration
	 * @param array $routage
	 * @return bool success
	 */
	public static function setRoute(array $routage) {
		if (self::checkRouteStructure($routage)) {
			WConfig::set('route.app', $routage[0]);
			WConfig::set('route.args', $routage[1]);
			return true;
		} else {
			return false;
		}
	}
	
	public static function getApp() {
		return WConfig::get('route.app');
	}
	
	public static function updateApp($app) {
		WConfig::set('route.app', $app);
	}
	
	public static function getArgs() {
		return WConfig::get('route.args');
	}
	
	public static function updateArgs(array $args) {
		WConfig::set('route.args', $args);
	}
}

?>
