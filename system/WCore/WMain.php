<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WMain.php 0003 26-10-2012 Fofif $
 * @package Wity
 */

require SYS_DIR.'WCore'.DS.'WController.php';
require SYS_DIR.'WCore'.DS.'WView.php';

class WMain {
	// Liste des applications accessibles
	private $apps;
	
	public function __construct() {
		// Chargement des configs
		$this->loadConfigs();
		
		// Route
		$this->route();
		
		// Paramétrage des sessions
		$this->setupSession();
		
		// Lang init
		$this->setupLang();
		
		// $this->log();
		
		// Execution de l'action
		$this->exec(WRoute::getApp());
	}
	
	/**
	 * Adds action to perform at the end of the script
	 */
	public function __destruct() {
		// Flush the notes waiting for their own view
		WNote::displayCustomView();
	}
	
	/**
	 * Exéxcution d'une application
	 */
	public function exec($app_name) {
		// App asked exists?
		if ($this->isApp($app_name)) {
			// App controller file
			$app_dir = APPS_DIR.$app_name.DS.'front'.DS;
			include $app_dir.'main.php';
			$class = str_replace('-', '_', ucfirst($app_name)).'Controller';
			
			// App's controller must inherit WController
			if (class_exists($class) && get_parent_class($class) == 'WController') {
				$controller = new $class();
				$controller->init($this, $app_dir);
				$controller->launch();
			} else {
				WNote::error('app_structure', "The application \"".$app_name."\" has to inherit WController abstract class.", 'display');
			}
		} else {
			WNote::error(404, "The page requested was not found.", 'display');
		}
	}
	
	/**
	 * Récupère la liste des apps
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
	 * Vérifie si une app existe
	 */
	public function isApp($app) {
		return !empty($app) && in_array($app, $this->getAppsList());
	}
	
	private function loadConfigs() {
		WConfig::load('config', SYS_DIR.'config'.DS.'config.php', 'php');
	}
	
	private function route() {
		WRoute::init();
		WRoute::route();
	}
	
	private function setupSession() {
		// Il suffit de l'instancier
		$session = WSystem::getSession();
		
		// Check de l'antiflood
		if (!$session->check_flood()) {
			$_POST = array();
		}
	}
	
	private function setupLang() {
		$lang_config = WConfig::get('config.lang');
		WLang::init();
		WLang::selectLang($lang_config);
	}
	
	private function log() {
		$file = fopen(WT_PATH.'log', 'a+');
		fwrite($file, "\n".@$_SESSION['userid']." - Route : ".$_SERVER['REQUEST_URI']." / ".date('d/m/Y H:i:s', time()));
		fclose($file);
	}
}

?>