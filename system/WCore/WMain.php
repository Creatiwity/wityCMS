<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WMain.php 0001 29-04-2012 Fofif $
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
		
        // $this->log();
		
		// Execution de l'action
		$this->exec(WRoute::getApp());
	}
	
	/**
	 * Exéxcution d'une application
	 */
	public function exec($app_name) {
		// Gestion de l'existence de l'appli
		if ($this->isApp($app_name)) {
			// Inclusion du fichier principal de l'appli
			include APPS_DIR.$app_name.DS.'front'.DS.'main.php';
			$class = str_replace('-', '_', ucfirst($app_name)).'Controller';
			
			if (class_exists($class) && get_parent_class($class) == 'WController') {
				$controller = new $class();
				$controller->init($this);
				$controller->launch();
			} else {
				WNote::info('app_structure', "L'application \"".$app_name."\" est mal structurée.", 'display');
			}
		} else {
			WNote::info(404, "L'application \"".$app_name."\" est introuvable.", 'display');
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
		return in_array($app, $this->getAppsList());
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
	
	private function log() {
		$file = fopen(WT_PATH.'log', 'a+');
		fwrite($file, "\n".@$_SESSION['userid']." - Route : ".$_SERVER['REQUEST_URI']." / ".date('d/m/Y H:i:s', time()));
		fclose($file);
	}
}

?>
