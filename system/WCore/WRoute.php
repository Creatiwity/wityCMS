<?php 
/**
 * WRoute.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WRoute calculates the route given in the URL to find out the right application to execute.
 * 
 * Traditionally, Apache URL Rewriting is used in WityCMS.
 * Example: the URL "http://mysite.com/wity/news/see/4" would be translated like this :
 * - param1 = "news" (this param will be used as Application name in WMain)
 * - param2 = "see" - in this case, this parameter is called the action of the application
 * - param3 = "4" - in this case, it may be the id of the news to display
 * 
 * WRoute can provide several informations about the URL of the page.
 * If we keep the example URL = http://mysite.com/wity/news/see/4
 * - Base = "http://mysite.com/wity" - Base contains the directory in which WityCMS is installed
 * - Dir = "/wity" - it is the directory in which WityCMS is installed (may be empty)
 * - Query = "/news/see/4"
 *     _ Params = array("news", "see", "4")
 * - URL = Base + Query - full URL of the page
 *       = "http://mysite.com/wity/news/see/4"
 * 
 * Notice that every route information given by WRoute is formatted with the slash located at the beginning,
 * not at the end of the variables (except for the query if there is one "/" in the end).
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-16-10-2013
 */
class WRoute {
	/**
	 * @todo Adds a fallback method without URLREWRITING http://MySite.com/index.php/News/1
	 */
	
	/**
	 * If the URL is http://mysite.com/wity/user/login and if WityCMS is executed in /wity, 
	 * then the $query will be set to "user/login".
	 */
	/**
	 * @var string Request string of the page
	 */
	private static $query;
	
	/**
	 * Initializes WRoute.
	 */
	public static function init() {
		// $_SERVER['REQUEST_URI'] contains the full URL of the page
		self::$query = str_replace(self::getDir(), '', $_SERVER['REQUEST_URI']);
		
		// Loading route config values
		WConfig::load('route', SYS_DIR.'config'.DS.'route.php', 'php');
	}
	
	/**
	 * Launches the calculation of the route to find out the app to execute.
	 * 
	 * @return array Current route
	 */
	public static function route() {
		$query = trim(self::$query, '/');
		
		// Checking the existence of a custom route
		$custom_routes = WConfig::get('route.custom');
		if (isset($custom_routes[$query])) {
			$route = $custom_routes[$query];
		} else {
			$route = self::parseURL($query);
		}
		
		self::setRoute($route);
		return $route;
	}
	
	/**
	 * Parses the web page URL.
	 * 
	 * @param string $url Web page URL
	 * @return array The route such as array("app_name", "param1", "param2")
	 */
	private static function parseURL($url) {
		// Cleaning
		$url = str_replace(array('index.php', '.html', '.htm'), '', $url);
		$url = preg_replace('#\?.*$#', '', $url); // Remove query string
		$url = trim($url, '/');
		
		return empty($url) ? array() : explode('/', $url);
	}
	
	/**
	 * Returns the full root location in which WityCMS is installed, as defined in /system/config/config.php.
	 * 
	 * If the website address is http://mysite.com/wity/user/login,
	 * it should return http://mysite.com/wity.
	 * 
	 * @return string the full root location of WityCMS
	 */
	public static function getBase() {
		return rtrim(WConfig::get('config.base'), '/');
	}
	
	/**
	 * Returns the partial WityCMS root directory.
	 * 
	 * If the website address is http://mysite.com/wity/user/login,
	 * it will return /wity.
	 * 
	 * @return string the partial root location of WityCMS
	 */
	public static function getDir() {
		// Remove the working directory of the script
		// example: $_SERVER['SCRIPT_NAME'] = http://mysite.com/wity/index.php
		$dir = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
		$dir = rtrim($dir, '/');
		return $dir;
	}
	
	/**
	 * Returns the query asked to WityCMS in the URL.
	 * 
	 * If the request URL is http://mysite.com/wity/user/login,
	 * it will return /user/login.
	 * 
	 * @return string the partial root location of WityCMS
	 */
	public static function getQuery() {
		return self::$query;
	}
	
	/**
	 * Returns the full URL of the page.
	 * 
	 * For example: http://mysite.com/wity/user/login
	 * 
	 * @return string the full URL
	 */
	public static function getURL() {
		return self::getBase().self::$query;
	}
	
	/**
	 * Returns the referer (the previous address).
	 * 
	 * @param bool $default true: if the referer is empty, returns the URL base; false: return ''
	 * @return string The referer
	 */
	public static function getReferer($default = true) {
		$base = self::getBase();
		
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $base) !== false) {
			return $_SERVER['HTTP_REFERER'];
		} else if ($default) {
			return $base;
		}
		
		return '';
	}
	
	/**
	 * Defines a custom route which is not following the regular application rules
	 * 
	 * <code>WRoute::defineCustomRoute('/test/', array('appName', 'param1', 'param2'));</code>
	 * 
	 * @param  string  $uri     the custom route to catch
	 * @param  array   $route   List of parameters (application name and parameters...)
	 * @return boolean true if route structure is valid, false otherwise
	 */
	public static function defineCustomRoute($uri, array $route) {
		// Checking the routage structure
		if (self::checkRouteStructure($route)) {
			$custom_routes = WConfig::get('route.custom');
			$custom_routes[$uri] = $route;
			WConfig::set('route.custom', $custom_routes);
			WConfig::save('route');
			return true;
		}
		return false;
	}
	
	/**
	 * Deletes a custom route.
	 * 
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
	 * Defines route values in the configuration.
	 * 
	 * @param array $route Current route in WityCMS that will be set in configuration class.
	 */
	public static function setRoute(array $route) {
		WConfig::set('route.request', $route);
	}
	
	/**
	 * Returns the current route.
	 * 
	 * @return string Current route in WityCMS
	 */
	public static function getRoute() {
		return WConfig::get('route.request');
	}
	
	public static function getDefault() {
		return WConfig::get('route.default');
	}
}

?>
