<?php
/**
 * WMain.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

require_once SYS_DIR.'WCore'.DS.'WController.php';
require_once SYS_DIR.'WCore'.DS.'WView.php';

/**
 * WMain is the main class that Wity launches at start-up.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-27-09-2013
 */
class WMain {
	/**
	 * Initializes config, route, session, lang and then executes the application.
	 */
	public function __construct() {
		// Loading config
		$this->loadConfigs();

		// Initializing the route
		$this->route();

		// Initializing sessions
		$this->setupSession();

		// Setup Timezone
		$this->setupTimezone();

		// Initializing lang
		WLang::init();
		WLang::declareLangDir(SYS_DIR.'lang');

		// Initializing WRetrever
		WRetriever::init();

		// Executes the application
		$this->exec();
	}

	/**
	 * Executes the main application and wrap it into a response for the client.
	 * The default response is the view of the main application included into a theme.
	 *
	 * If the user adds /m/ in the beginning of the route, the response will be the serialized
	 * model of the application in a JSON structure for instance.
	 */
	private function exec() {
		// Get the application name
		$route = WRoute::route();

		$response = new WResponse();
		$model = WRetriever::getModel($route['app'], $route['params'], false);
		switch ($route['mode']) {
			case 'm': // Only model
				$response->renderModel($model);
				break;

			case 'v': // Only view
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$response->renderView($model, $view);
				break;

			case 'mv': // Model + View
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$response->renderModelView($model, $view);
				break;

			case 'o': // Only Model but nothing returned
				break;

			default: // Render in a theme
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$theme = ($route['admin']) ? 'admin-bootstrap': WConfig::get('config.theme');
				$response->render($view, $theme, $model);
				break;
		}
	}

	/**
	 * Loads WConfig
	 */
	private function loadConfigs() {
		WConfig::load('config', CONFIG_DIR.'config.php', 'php');
	}

	/**
	 * Initializes the route.
	 * Prevents browser from trying to load a physical file.
	 */
	private function route() {
		WRoute::init();

		// Checks if the browser tried to load a physical file
		$error = false;
		$query = WRoute::getQuery();
		$length = strlen($query);
		if (substr($query, $length-3, 1) == '.') {
			$ext = substr($query, $length-2, 2);
			if ($ext == 'js') {
				$error = true;
			}
		} else if (substr($query, $length-4, 1) == '.') {
			$ext = substr($query, $length-3, 3);
			if (in_array($ext, array('js', 'css', 'png', 'jpg', 'gif', 'ico', 'svg', 'eot', 'ttf'))) {
				$error = true;
			}
		} else if (substr($query, $length-5, 1) == '.') {
			$ext = substr($query, $length-4, 4);
			if (in_array($ext, array('jpeg', 'woff'))) {
				$error = true;
			}
		}
		if ($error) {
			$route = WRoute::route();

			if ($route['app'] != 'media') {
				header('HTTP/1.0 404 Not Found');
				WNote::error(404, 'The resource could not be found.', 'die');
			}
		}
	}

	/**
	 * Initializes session and check the flood condition
	 */
	private function setupSession() {
		// Instanciates it
		$session = WSystem::getSession();

		// Anti-flood checking
		if (!$session->check_flood()) {
			$_POST = array();
		}
	}

	/**
	 * Setup WityCMS timezone for dates
	 * Will change PHP and MySQL configuration
	 */
	private function setupTimezone() {
		// Get client timezone
		$timezone = WDate::getUserTimezone();

		// Define default GMT timezone if the Server's config is empty
		$server_timezone = ini_get('date.timezone');
		if (empty($server_timezone) || $server_timezone != $timezone->getName()) {
			date_default_timezone_set($timezone->getName());
		}

		// Calculates the offset to GMT in Hours
		$offset = $timezone->getOffset(new DateTime('now', new DateTimeZone('UTC')))/3600;
		$plus = ($offset >= 0) ? '+' : '';

		// Change MySQL timezone
		WSystem::getDB()->query("SET time_zone = '".$plus.$offset.":00'");
	}
}

?>
