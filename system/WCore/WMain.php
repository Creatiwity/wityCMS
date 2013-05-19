<?php 
/**
 * WMain.php
 */

defined('IN_WITY') or die('Access denied');

require SYS_DIR.'WCore'.DS.'WController.php';
require SYS_DIR.'WCore'.DS.'WView.php';

/**
 * WMain is the main class that Wity launches at startup
 *
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-17-01-2013
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
		
		// Initializing lang
		WLang::init();
		
		// Exec application
		$this->exec(WRoute::getApp());
		//echo WRetriever::getView(WRoute::getApp());
	}
	
	/**
	 * This function will setup the whole WityCMS response
	 * Find and load the theme
	 */
	private function exec($app_name) {
		$view = WRetriever::getView(WRoute::getApp());
		
		$response = new WResponse(WConfig::get('config.theme'));
		$response->render($view);
	}
	
	/**
	 * Loads WConfig
	 */
	private function loadConfigs() {
		WConfig::load('config', SYS_DIR.'config'.DS.'config.php', 'php');
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
}

?>