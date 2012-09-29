<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: WCore/WRoute.php 0003 29-09-2012 Fofif $
 */

class WRoute {
	/**
	 * @todo Un systeme sans URLREWRITING http://MonSite.fr/index.php/News/1
	 */
	
	/**
	 * Request string of the page
	 * ex: the URL is http://mysite.fr/wity/user/login
	 * If wity is executed in /wity/, then the $query will be set to "user/login"
	 */
	public static $query;
	
	public static function init() {
		$dir = self::getDir();
		if ($dir != '/') {
			self::$query = str_replace($dir, '', $_SERVER['REQUEST_URI']);
		} else {
			self::$query = $_SERVER['REQUEST_URI'];
		}
		
		// Chargement des valeurs de config du routage
		WConfig::load('route', SYS_DIR.'config'.DS.'route.php', 'php');
	}
	
	/**
	 * Launches the calculation of the route to find out the app to execute
	 */
	public static function route() {
		// Vérification de l'existence d'un routage perso
		$perso = WConfig::get('route.perso');
		$query = trim(self::$query, '/');
		if (isset($perso[$query])) {
			self::setRoute($perso[$query]);
		} else {
			// Chargement de la config URL
			$routage = self::parseURL(self::$query);
			if (!empty($routage)) {
				self::setRoute($routage);
			} else {
				// Si rien n'a été fourni, chargement du routage par défaut
				self::setRoute(WConfig::get('route.default'));
			}
		}
	}
	
	/**
	 * Returns the full root location in which wity is installed, as defined in /system/config/config.php
	 * ex: if the website adress is http://mysite.fr/wity/user/login,
	 * it should return http://mysite.fr/wity/
	 */
	public static function getBase() {
		return rtrim(WConfig::get('config.base'), '/').'/';
	}
	
	/**
	 * Obtention de l'url du dossier où se situe wity
	 * ex: if the website adress is http://mysite.fr/wity/user/login
	 * it will return /wity/
	 */
	public static function getDir() {
		return substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
	}
	
	/**
	 * Returns the FULL URL of the page
	 * ex: http://mysite.fr/wity/user/login
	 */
	public static function getURL() {
		return self::getBase().self::$query;
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
	 * Defines a personnal route which is not following the regular application rules
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
	 * Parse the webpage URL
	 * 
	 * @param string $url Webpage URL (ex: http://MonSite.fr/News/Read/1)
	 * @return array The route (ex: array('app' => "News", 'args' => array(1)))
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
	 * 
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
