<?php 
/**
 * WMain.php
 */

defined('IN_WITY') or die('Access denied');

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
		$params = WRoute::getRoute();
		$app_name = array_shift($params);
		
		$response = new WResponse();
		switch (WConfig::get('route.mode')) {
			case 'm':
				$response->renderModel(WRetriever::getModel($app_name, $params, false));
				break;
			
			case 'v':
				$response->renderView(WRetriever::getView($app_name, $params, false));
				break;
			
			case 'mv':
				$response->renderModelView(
					WRetriever::getModel($app_name, $params, false),
					WRetriever::getView($app_name, $params, false)
				);
				break;
			
			default:
				// Render as a theme
				$response->render(
					WRetriever::getView($app_name, $params, false), 
					WConfig::get('config.theme')
				);
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
	 * Initializes WRoute and calculate the response mode.
	 */
	private function route() {
		// Setup the route
		WRoute::init();
		$route = WRoute::route();
		
		// Extract the mode if exists
		$mode = 'theme';
		if (isset($route[0]) && in_array($route[0], array('m', 'v', 'mv'))) {
			$mode = array_shift($route);
			
			if (empty($route)) {
				$route = WRoute::getDefault();
			}
			
			// Update the route without the mode
			WRoute::setRoute($route);
		}
		
		// Admin route
		// In WityCMS, to trigger an admin app, the first route key should be "admin/news"
		if (isset($route[0]) && $route[0] == 'admin') {
			array_shift($route); // remove "admin" from first key
			$app = array_shift($route);
			if (empty($app)) { // default admin route
				$route = WConfig::get('route.admin');
			} else {
				$app = 'admin/'.$app;
				array_unshift($route, $app);
			}
			
			// Update the route with admin settings
			WRoute::setRoute($route);
		}
		
		WConfig::set('route.mode', $mode);
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
