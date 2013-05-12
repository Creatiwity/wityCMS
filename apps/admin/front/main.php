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
		if (!WSession::isConnected()) { // Display login form if not connected
			WNote::info('admin_login_required', "Cette zone nécessite une authentification.", 'assign');
			$this->wity->exec('user');
		} else if ($this->checkAdminAccess()) {
			$this->routeAdmin();
			$this->appAsked = WRoute::getApp();
			if (!empty($this->appAsked) && $this->appAsked != 'admin') {
				// Load admin controller of the app
				$app_dir = APPS_DIR.$this->appAsked.DS.'admin'.DS;
				include $app_dir.'main.php';
				$app_class = ucfirst($this->appAsked).'AdminController';
				
				if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
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
					WNote::error('app_structure', "The application \"".$this->appAsked."\" has to have a main class inheriting from WController abstract class.", 'display');
				}
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
	 * Checks user access to the administration
	 * 
	 * @return bool
	 */
	private function checkAdminAccess() {
		if (!empty($_SESSION['access'])) {
			if ($_SESSION['access'] == 'all') {
				return true;
			}
			foreach ($_SESSION['access'] as $app => $perms) {
				if (in_array('admin', $perms) && array_key_exists($app, $this->getAdminApps())) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Retrieves app having an admin side
	 * 
	 * @return array array(app_name => manifest)
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
	 * Updates the route for the admin app
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
					$apps_keys = array_keys($apps);
					WRoute::setRoute(array(array_shift($apps_keys), array()));
				}
			}
		}
	}
	
	/**
	 * Configurates the admin template
	 */
	private function configTheme() {
		// Matching the views
		if (!is_null($this->appController)) {
			$this->setView($this->appController->getView());
		}
		
		// Config theme
		WConfig::set('config.theme', 'admin-bootstrap');
		$this->view->setTheme('admin-bootstrap');
		
		// These are template variables => direct assign in WTemplate
		$tpl = WSystem::getTemplate();
		$tpl->assign('appsList', $this->getAdminApps());
		$tpl->assign('userNickname', $_SESSION['nickname']);
		
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
				isset($manifest['admin'][$action_asked]) ? ' &raquo; '.WLang::get($manifest['admin'][$action_asked]['desc']) : ''
			));
		} else { // No admin app loaded: this is the admin homepage
			$tpl->assign(array(
				'appSelected' => '',
				'actionsList' => array(),
				'actionAsked' => ''
			));
		}
	}
}

?>
