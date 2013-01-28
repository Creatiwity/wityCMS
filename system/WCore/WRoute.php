<?php 
/**
 * WRoute.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WRoute calculates the route given in the URL to find out the right application to execute.
 * 
 * Traditionnally, Apache URL Rewriting is used in Wity.
 * Example: the URL "http://mysite.fr/news/see/4" would be translated like this :
 * - app = "news"
 * - arg1 = "see" - in this case, this argument is called the action of the application)
 * - arg2 = "4" - in this case, it may be the id of the news to display)
 * 
 * WRoute can provide several informations about the URL of the page.
 * If we keep the example URL = http://mysite.fr/news/see/4
 * - Base = "http://mysite.fr"
 * - Dir = "" - it is the directory in which Wity is installed
 * - Query = "/news/see/4"
 *     _ App = "news"
 *     _ Args = array("see", "4")
 * - URL = Base + Query - full URL of the page
 *       = "http://mysite.fr/wity/news/see/4"
 * 
 * Notice that every route information given by WRoute is formated with the slash provided in the begining,
 * not in the end of the variables (except for the query if there is one "/" in the end).
 * 
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-23-01-2013
 */
class WRoute {
	/**
	 * @todo Adds a fallback method without URLREWRITING http://MySite.com/index.php/News/1
	 */
	
	/**
	 * If the URL is http://mysite.fr/wity/user/login
	 * and if wity is executed in /wity/, then the $query will be set to "user/login"
	 */
	/**
	 * @var string Request string of the page
	 */
	private static $query;
	
	/**
	 * Initializes WRoute
	 */
	public static function init() {
		// $_SERVER['REQUEST_URI'] contains the full URL of the page
		self::$query = str_replace(self::getDir(), '', $_SERVER['REQUEST_URI']);
		
		// Loading route config values
		WConfig::load('route', SYS_DIR.'config'.DS.'route.php', 'php');
	}
	
	/**
	 * Launches the calculation of the route to find out the app to execute
	 */
	public static function route() {
		$query = trim(self::$query, '/');
		
		// Checking the existency of a custom route
		$custom_routes = WConfig::get('route.custom');
		if (isset($custom_routes[$query])) {
			self::setRoute($custom_routes[$query]);
		} else {
			// Loading URL config
			$routage = self::parseURL(self::$query);
			if (!empty($routage)) {
				self::setRoute($routage);
			} else {
				// If nothing found, launch the default route
				self::setRoute(WConfig::get('route.default'));
			}
		}
	}
	
	/**
	 * Returns the full root location in which WityCMS is installed, as defined in /system/config/config.php
	 * 
	 * If the website adress is http://mysite.fr/wity/user/login,
	 * it should return http://mysite.fr/wity
	 * 
	 * @return string the full root location of WityCMS
	 */
	public static function getBase() {
		return rtrim(WConfig::get('config.base'), '/');
	}
	
	/**
	 * Returns the partial WityCMS root directory
	 * 
	 * If the website adress is http://mysite.fr/wity/user/login
	 * it will return /wity
	 * 
	 * @return string the partial root location of WityCMS
	 */
	public static function getDir() {
		// Remove the working directory of the script
		// $_SERVER['SCRIPT_NAME'] = http://mysite.fr/wity/index.php
		$dir = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
		$dir = rtrim($dir, '/');
		return $dir;
	}
	
	/**
	 * Returns the query asked to Wity in the URL
	 * 
	 * If the request URL is http://mysite.fr/wity/user/login
	 * it will return /user/login
	 * 
	 * @return string the partial root location of WityCMS
	 */
	public static function getQuery() {
		return self::$query;
	}
	
	/**
	 * Returns the full URL of the page
	 * 
	 * For example: http://mysite.fr/wity/user/login
	 * 
	 * @return string the full URL
	 */
	public static function getURL() {
		return self::getBase().self::$query;
	}
	
	/**
	 * Return the referer (the previous address)
	 * 
	 * @return string the referer
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
	 * Defines a custom route which is not following the regular application rules
	 * 
	 * <code>
	 *   WRoute::defineCustomRoute('/test/', array(
	 *     'appName',
	 *     array('arg1', 'arg2')
	 *   ));
	 * </code>
	 * 
	 * @see WConfig::get()
	 * @see WConfig::set()
	 * @see WConfig::save()
	 * 
	 * @param  string  $uri     the custom route to catch
	 * @param  array   $routage application that will be launched with its arguments
	 * @return boolean true if route structure is valid, false otherwise
	 */
	public static function defineCustomRoute($uri, array $routage) {
		// Checking the routage structure
		if (self::checkRouteStructure($routage)) {
			$custom_routes = WConfig::get('route.custom');
			$custom_routes[$uri] = $routage;
			WConfig::set('route.custom', $custom_routes);
			WConfig::save('route');
			return true;
		}
		return false;
	}
	
	/**
	 * Removing a custom route
	 * 
	 * @see WConfig::get()
	 * @param string $uri the custom route to remove
	 */
	public static function deleteCustomRoute($uri) {
		if (!is_null(WConfig::get('route.custom.'.$uri))) {
			$custom_routes = WConfig::get('route.custom');
			unset($custom_routes[$uri]);
			WConfig::set('route.custom', $custom_routes);
			WConfig::save('route');
		}
	}
	
	/**
	 * Parse the webpage URL
	 * 
	 * @param string $url webpage URL (ex: http://MySite.com/News/Read/1)
	 * @return array the route (ex: array('app' => "News", 'args' => array(1)))
	 */
	private static function parseURL($url) {
		$routage = array();
		
		// Cleaning
		$url = trim($url, '/');
		$url = str_replace(array('index.php', '.html', '.htm'), '', $url);
		$url = preg_replace('#\?.*$#', '', $url); // Nettoyage des query string
		
		$array = explode('/', $url);
		// Given application name
		if (!empty($array[0])) {
			$routage[] = strtolower(array_shift($array));
			if (sizeof($array) > 0) {
				// Storing arguments
				$routage[] = $array;
			} else {
				$routage[] = array();
			}
		}
		return $routage;
	}
	
	/**
	 * Checks if the route structure is good
	 * 
	 * A good structure example :
	 * <code>$routage = array('AppName', array('argument1', 'argument2'));</code>
	 * 
	 * @param array $routage the route
	 * @return boolean true if the structure is good, false otherwise
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
	 * Defines route values in the configuration
	 * 
	 * @see WConfig::set()
	 * @param array $routage routes that will be defined in the configuration
	 * @return boolean true if route structure is good and defined, false otherwise
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
	
	/**
	 * Returns the current applcation name
	 * 
	 * @return string current application name
	 */
	public static function getApp() {
		return WConfig::get('route.app');
	}
	
	/**
	 * Changes the current application to $app
	 * 
	 * @param string $app the new application name
	 */
	public static function updateApp($app) {
		WConfig::set('route.app', $app);
	}
	
	/**
	 * Returns the arguments of the current application
	 * 
	 * @return array current application arguments
	 */
	public static function getArgs() {
		return WConfig::get('route.args');
	}
	
	/**
	 * Changes the arguments of the current application
	 *  
	 * @param array $args the new arguments
	 */
	public static function updateArgs(array $args) {
		WConfig::set('route.args', $args);
	}
}

?>
