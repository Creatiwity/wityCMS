<?php
/**
 * WRoute.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WRoute calculates the route given in the URL to find out the right application to execute.
 *
 * <p>Traditionally, Apache URL Rewriting is used in wityCMS.
 * Example: the URL "http://mysite.com/wity/news/see/4?published=1" would be translated this way:</p>
 * <ul>
 *    <li>app = "news" (this param will be used as Application name in WMain)</li>
 *    <li>param1 = "see" - in this case, this parameter is called the action of the application</li>
 *    <li>param2 = "4" - in this case, it may be the id of the news to display</li>
 * </ul>
 *
 * <p>WRoute provides several informations about the URL of the page.
 * If we keep the example URL = http://mysite.com/wity/news/see/4?published=1</p>
 * <ul>
 *     <li>Base = "http://mysite.com/wity/" - Base contains the host + directory in which wityCMS is installed</li>
 *     <li>Dir = "/wity/" - it is the directory in which wityCMS is installed (contains at least '/')</li>
 *     <li>Query = "news/see/4" [Params = array("news", "see", "4")]</li>
 *     <li>QueryString = "published=1"</li>
 *     <li>URL = Base + Query (= "http://mysite.com/wity/news/see/4?published=1") - full URL of the page</li>
 * </ul>
 *
 * <p>Notice that every route information given by WRoute is formatted with the slash located at the beginning,
 * not at the end of the variables (except for the query if there is one "/" in the end).</p>
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class WRoute {
	/**
	 * If the URL is http://mysite.com/wity/user/login and if wityCMS is executed in /wity,
	 * then the $query will be set to "user/login".
	 *
	 * @var string Request string of the page
	 */
	private static $query;

	/**
	 * Stores the query string given in the URL.
	 * Example: "published=1"
	 *
	 * @var string URL Query String
	 */
	private static $queryString;

	/**
	 * Stores the calculated route.
	 *
	 * @var array
	 */
	private static $route;

	/**
	 * Initializes WRoute.
	 */
	public static function init() {
		self::$query = $_SERVER['REQUEST_URI'];

		// $_SERVER['REQUEST_URI'] contains the full URL of the page
		$dir = self::getDir();
		if ($dir != '/') {
			self::$query = str_replace($dir, '', $_SERVER['REQUEST_URI']);
		}

		// Cleansing
		self::$query = ltrim(self::$query, '/');
		self::$query = str_replace(array('index.php', 'index.html'), '', self::$query);

		// Extract query string
		$split_query = explode('?', self::$query);
		if (count($split_query) > 1) {
			self::$query = $split_query[0];
			self::$queryString = $split_query[1];
		}

		// Loading route config values
		WConfig::load('route', SYS_DIR.'config'.DS.'route.php', 'php');
	}

	/**
	 * Launches the calculation of the current Route.
	 *
	 * @return array The route
	 */
	public static function route() {
		$route = self::$route;
		if (!empty($route)) {
			return $route;
		}

		$route = self::parseURL(self::$query);
		$mode = $route['mode'];

		// Remove the mode from the URL
		if ($route['mode'] != '') {
			$clean_query = str_replace($route['mode'].'/', '', self::$query);
		} else {
			$clean_query = self::$query;
		}
		$clean_query = rtrim($clean_query, '/');

		// Checking the existence of a custom route
		$custom_routes = WConfig::get('route.custom');
		if (isset($custom_routes[$clean_query])) {
			$route = self::parseURL($custom_routes[$clean_query]);
			$route['mode'] = $mode;
		} else if (empty($route['app'])) {
			// Use default route
			if ($route['admin']) {
				$route = self::parseURL(WConfig::get('route.default_admin'));
			} else {
				$route = self::parseURL(WConfig::get('route.default_front'));
			}

			$route['mode'] = $mode;
		}

		self::$route = $route;

		return $route;
	}

	/**
	 * Parses a URL to a route format.
	 *
	 * @param string $url A web page URL such as "news/see/13/"
	 * @return array URL translated into a route ["app", "params", "mode", "admin"]
	 */
	public static function parseURL($url) {
		$route = array(
			'app'    => '',
			'params' => array(),
			'mode'   => '',
			'admin'  => false
		);

		if (is_string($url)) {
			$url = trim($url, '/');

			if (!empty($url)) {
				$params = explode('/', $url);

				// Extract the mode if exists
				if (isset($params[0]) && in_array($params[0], array('m', 'v', 'mv', 'o'))) {
					$route['mode'] = array_shift($params);
				}

				// Extract the app
				$app = array_shift($params);
				if (!empty($app)) {
					// Admin route
					if ($app == 'admin') {
						$route['admin'] = true;

						$app = array_shift($params);
						if (!empty($app)) {
							// In wityCMS, to trigger an admin app, the app must be equal to "admin/news"
							$route['app'] = 'admin/'.$app;
						}
					} else {
						$route['app'] = $app;
					}
				}

				$route['params'] = $params;
			}
		}

		return $route;
	}

	/**
	 * Returns the domain from which the user tried to acess wityCMS.
	 *
	 * If the site is running on http://mysite.com/wity/,
	 * it should return "mysite.com".
	 *
	 * @return string Domain name
	 */
	public static function getDomain() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Returns the full root location in which wityCMS is installed, as defined in /system/config/config.php.
	 *
	 * If the website address is http://mysite.com/wity/user/login,
	 * it should return http://mysite.com/wity/.
	 *
	 * @return string the full root location of wityCMS
	 */
	public static function getBase() {
		return rtrim(WConfig::get('config.base'), '/').'/';
	}

	/**
	 * Returns the partial wityCMS root directory.
	 *
	 * If the website address is "http://mysite.com/wity/news/see/4?published=1",
	 * it will return "/wity/".
	 *
	 * @return string The partial root location of wityCMS
	 */
	public static function getDir() {
		// Remove the working directory of the script
		// example: $_SERVER['SCRIPT_NAME'] = /wity/index.php
		$dir = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);

		return $dir;
	}

	/**
	 * Returns the query asked to wityCMS in the URL.
	 *
	 * If the request URL is "http://mysite.com/wity/news/see/4?published=1",
	 * it will return "/news/see/4".
	 *
	 * @return string The partial root location of wityCMS
	 */
	public static function getQuery() {
		return self::$query;
	}

	/**
	 * Returns the query string given in URL (without '?' char).
	 *
	 * If the request URL is "http://mysite.com/wity/news/see/4?published=1",
	 * it will return "published=1".
	 *
	 * @return string The partial root location of wityCMS
	 */
	public static function getQueryString() {
		return self::$queryString;
	}

	/**
	 * Returns the full URL of the page.
	 *
	 * For example: "http://mysite.com/wity/news/see/4?published=1"
	 *
	 * @return string The full URL
	 */
	public static function getURL() {
		return self::getBase().self::$query.'?'.self::$queryString;
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
	 * Defines a custom route to redirect to a specific application.
	 *
	 * <code>WRoute::defineCustomRoute('test', 'news/see/13');</code>
	 *
	 * @param  string  $uri         The custom route to catch
	 * @param  array   $redirection Redirection URI
	 * @return boolean true if the redirection was applied
	 */
	public static function defineCustom($uri, $redirection) {
		$uri = trim($uri, '/');
		if (!empty($uri)) {
			WConfig::set('route.custom.'.$uri, $redirection);
			WConfig::save('route');

			return true;
		}

		return false;
	}

	/**
	 * Deletes a custom route.
	 *
	 * @param string $uri The custom route to remove
	 */
	public static function deleteCustom($uri) {
		$uri = trim($uri, '/');
		if (!empty($uri) && !is_null(WConfig::get('route.custom.'.$uri))) {
			$custom_routes = WConfig::get('route.custom');
			unset($custom_routes[$uri]);
			WConfig::set('route.custom', $custom_routes);
			WConfig::save('route');
		}
	}
}

?>
