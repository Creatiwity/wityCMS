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
	 * @var array(string)|null Stores all the accessible applications
	 */
	private $apps = null;
	
	/**
	 * Initializes config, route, session, lang and then exec the application
	 */
	public function __construct() {
		// Loading config
		$this->loadConfigs();
		
		// Route
		$this->route();
		
		// Initializing sessions
		$this->setupSession();
		
		// Setup Timezone
		$this->setupTimezone();
		
		// Initializing lang
		WLang::init();
		
		// Initializing WRetrever
		WRetriever::init();
		
		// Exec application
		$this->exec();
	}
	
	/**
	 * This function will setup the whole WityCMS response
	 * Find and load the theme
	 */
	private function exec() {
		// Setup the main app to execute
		$app_name = WRoute::getApp();
		$params = WRoute::getArgs();
		$mode = WConfig::get('route.response');
		
		if (!empty($mode)) {
			$format = strtoupper(WConfig::get('route.format'));
			if (empty($format) || !in_array($format, array('JSON', 'XML', 'YAML'))) {
				$format = 'JSON';
			}
			
			$model = WRetriever::getModel($app_name, $params, false);
			if ($mode == 'm') {
				$response = array(
					'app-name' => $app_name,
					'model' => $model
				);
			} else if ($mode == 'v') {
				$view = WRetriever::getView($app_name, $params, false)->render();
				
				$response = array(
					'app-name' => $app_name,
					'view'  => $view
				);
			} else if ($mode == 'mv') {
				$view = WRetriever::getView($app_name, $params, false)->render();
				
				$response = array(
					'app-name' => $app_name,
					'model' => $model,
					'view'  => $view
				);
			}
			
			echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		} else {
			// Get the view
			$view = WRetriever::getView($app_name, $params, false);
			
			// Render the final response
			$response = new WResponse(WConfig::get('config.theme'));
			$response->render($view);
		}
	}
	
	/**
	 * Loads WConfig
	 */
	private function loadConfigs() {
		WConfig::load('config', CONFIG_DIR.'config.php', 'php');
	}
	
	/**
	 * Loads WRoute
	 */
	private function route() {
		WRoute::init();
		WRoute::route();
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
