<?php
/**
 * admin/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * AdminController handles admin applications
 * 
 * @package Apps\Admin
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-17/01/2013
 */
class AdminController extends WController {
	/**
	 * @var Model of admin app
	 */
	private $model;
	
	/**
	 * @var Name of the admin application asked
	 */
	private $appAsked;
	
	/**
	 * @var Instance of the admin app beeing executed
	 */
	private $appController = null;
	
	public function init(WMain $wity, $context) {
		// Change admin context
		$context['admin'] = true;
		parent::init($wity, $context);
	}
	
	/**
	 * Main method
	 */
	public function launch() {
		if (!WSession::isConnected()) {
			// Affichage du formulaire d'autentification
			WNote::info('admin_login_required', "Cette zone nécessite une authentification.", 'assign');
			$this->wity->exec('user');
		} else if ($this->checkAdminAccess()) {
			$this->routeAdmin();
			$this->appAsked = WRoute::getApp();
			if ($this->appAsked != 'admin') {
				// Load admin controller of the app
				$app_dir = APPS_DIR.$this->appAsked.DS.'admin'.DS;
				include $app_dir.'main.php';
				$app_class = ucfirst($this->appAsked).'AdminController';
				
				// Create context
				$context = array(
					'name'       => $this->appAsked,
					'directory'  => $app_dir,
					'controller' => $app_class,
					'admin'      => true
				);
				
				$this->appController = new $app_class();
				$this->appController->init($this->wity, $context);
				
				// Config Template
				$this->configTheme();
				
				// Execute
				$this->appController->launch();
			} else {
				// Config du template
				$this->configTheme();
				WNote::error('admin_no_access', "No suitable application to display was found. Please, select one from the menu.", 'display');
			}
		} else {
			WNote::error('admin_access_forbidden', "You do not have access to the administration.", 'display');
		}
	}
	
	/**
	 * Vérifie si l'utilisateur a accès à l'administration
	 * 
	 * @return bool
	 */
	private function checkAdminAccess() {
		return !empty($_SESSION['access']);
	}
	
	/**
	 * Récupère la liste des applis administrables
	 * 
	 * @return array
	 */
	public function getAdminApps() {
		static $admin_apps = array();
		if (empty($admin_apps)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			foreach ($apps as $app) {
				$app_name = strtolower(basename($app));
				if (file_exists($app.DS.'admin'.DS.'main.php') && $this->hasAccess($app_name, '', true)) {
					$admin_apps[$app_name] = $this->loadManifest($app_name);
				}
			}
		}
		return $admin_apps;
	}
	
	/**
	 * Met à jour le routage de l'appli admin
	 * 
	 * @return void
	 */
	private function routeAdmin() {
		// Le nom de l'appli à administrer se trouve dans les arguments
		$args = WRoute::getArgs();
		$app = array_shift($args);
		if (!empty($app) && $this->hasAccess($app, '', true)) {
			// Mise à jour du routage
			WRoute::updateApp($app);
			WRoute::updateArgs($args); // Nettoyage des arguments
		} else {
			$default = WConfig::get('route.admin');
			// Get the first arg of the route which is the action to load
			$action = isset($default[1][0]) ? $default[1][0] : '';
			if ($this->hasAccess($default[0], $action, true)) {
				WRoute::setRoute($default);
			} else {
				// Select a random app to display
				$apps = $this->getAdminApps();
				if (!empty($apps)) {
					WRoute::setRoute(array(array_shift(array_keys($apps)), array()));
				}
			}
		}
	}
	
	/**
	 * Configuration du thème de l'admin
	 * 
	 * @void
	 */
	private function configTheme() {
		// Matching the views
		if (!is_null($this->appController)) {
			$this->setView($this->appController->getView());
		}
		
		// Config theme
		WConfig::set('config.theme', 'admin');
		$this->view->setTheme('admin');
		
		// Ce sont des variables de template et non de view
		// Il est donc nécessaire des les assigner directement dans le moteur
		$tpl = WSystem::getTemplate();
		$tpl->assign('appsList', $this->getAdminApps());
		
		// Pseudonyme de l'utilisateur
		$tpl->assign('userNickname', $_SESSION['nickname']);
		
		// Aucune application n'a été chargée
		// On est donc sur la page d'accueil de l'admin
		if (!is_null($this->appController)) {
			$manifest = $this->appController->getManifest();
			$action_asked = $this->appController->getAskedAction();
			
			$tpl->assign(array(
				'appSelected' => $this->appAsked,
				'actionsList' => $manifest['admin'],
				'actionAsked' => $action_asked
			));
			$this->view->assign('page_title', sprintf('Admin &raquo; %s%s',
				ucwords($manifest['name']),
				isset($manifest['admin'][$action_asked]) ? ' &raquo; '.$manifest['admin'][$action_asked]['lang'] : ''
			));
		} else {
			$tpl->assign(array(
				'appSelected' => '',
				'actionsList' => array(),
				'actionAsked' => ''
			));
		}
	}
}

?>
