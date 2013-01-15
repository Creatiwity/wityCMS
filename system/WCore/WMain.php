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
 * @version 0.3-14-10-2012
 
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
		$this->setupLang();
		
		// $this->log();
		
		// exec application
		$this->exec(WRoute::getApp());
	}
	
	/**
	 * Flush the notes waiting for their own view, and then destroys WMain instance
	 */
	public function __destruct() {
		// Flush the notes waiting for their own view
		WNote::displayCustomView();
	}
	
    /**
     * If found, execute the application in the apps/$app_name directory
     * 
     * @param string $app_name name of the application that will be launched
     */
	public function exec($app_name) {
		// App asked exists?
		if ($this->isApp($app_name)) {
			// App controller file
			$app_dir = APPS_DIR.$app_name.DS.'front'.DS;
			include $app_dir.'main.php';
			$app_class = str_replace('-', '_', ucfirst($app_name)).'Controller';
			
			// App's controller must inherit WController
			if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
				$context = array(
					'name'       => $app_name,
					'directory'  => $app_dir,
					'controller' => $app_class,
					'admin'      => false
				);
				
				$controller = new $app_class();
				$controller->init($this, $context);
				$controller->launch();
			} else {
				WNote::error('app_structure', "The application \"".$app_name."\" has to have a main class inheriting WController abstract class.", 'display');
			}
		} else {
			WNote::error(404, "The page requested was not found.", 'display');
		}
	}
	
	/**
	 * Returns a list of applications that contains a main.php file in their front directory
	 * 
	 * @return array(string)
	 */
	public function getAppsList() {
		if (empty($this->apps)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			$this->apps = array();
			foreach ($apps as $appDir) {
				if (file_exists($appDir.DS.'front'.DS.'main.php')) {
					$this->apps[] = basename($appDir);
				}
			}
		}
		return $this->apps;
	}
	
    /**
     * Returns application existence
     * 
     * @param string $app
     * @return bool true if $app exists, false otherwise
     */
	public function isApp($app) {
		return !empty($app) && in_array($app, $this->getAppsList());
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
	
    /**
     * Loads lang config
     */
	private function setupLang() {
		$lang_config = WConfig::get('config.lang');
		WLang::init();
		WLang::selectLang($lang_config);
	}
	
    /**
     * Log activity in a file, DEBUG ONLY
     */
	private function log() {
		$file = fopen(WT_PATH.'log', 'a+');
		fwrite($file, "\n".@$_SESSION['userid']." - Route : ".$_SERVER['REQUEST_URI']." / ".date('d/m/Y H:i:s', time()));
		fclose($file);
	}
}

?>