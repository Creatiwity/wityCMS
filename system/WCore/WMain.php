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
		WRoute::init();
		
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
		$route = WRoute::route();
		
		$response = new WResponse();
		switch ($route['mode']) {
			case 'm': // Only model
				$response->renderModel(WRetriever::getModel($route['app'], $route['params'], false));
				break;
			
			case 'v': // Only view
				$response->renderView(
					WRetriever::getModel($route['app'], $route['params'], false),
					WRetriever::getView($route['app'], $route['params'], false)
				);
				break;
			
			case 'mv': // Model + View
				$response->renderModelView(
					WRetriever::getModel($route['app'], $route['params'], false),
					WRetriever::getView($route['app'], $route['params'], false)
				);
				break;
			
			default: // Render in a theme
				$response->render(
					WRetriever::getView($route['app'], $route['params'], false), 
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
