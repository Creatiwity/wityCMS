<?php
/**
 * Admin Application - Admin Controller - admin/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * AdminController is a controller which gives access to admin applications.
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-17-01-2013
 */
class AdminController extends WController {
	/**
	 * @var Name of the admin application asked
	 */
	private $appAsked;
	
	/**
	 * @var Instance of the admin app beeing executed
	 */
	private $appController = null;
	
	/**
	 * Main method
	 */
	public function launch(array $params = array()) {
		if (!WSession::isConnected()) { // Display login form if not connected
			WNote::info('admin_login_required', "Cette zone nécessite une authentification.", 'assign');
			$userView = WRetriever::getView('user', array('login'));
			$this->setView($userView);
		} else if ($this->checkAdminAccess()) {
			$this->appAsked = array_shift($params);
			
			// Use default
			if (empty($this->appAsked)) {
				$default = WConfig::get('route.admin');
				$this->appAsked = array_shift($default);
				// Get the first arg of the route which is the action to load
				$action = isset($default[0]) ? $default[0] : '';
				
				if ($this->hasAccess($this->appAsked, $action, true)) {
					$model = $this->exec($default);
				} else {
					// Select a random app to display
					$apps = array_keys($this->getAdminApps());
					$this->appAsked = $apps[0];
					$model = $this->exec($params);
				}
			} else if ($this->hasAccess($this->appAsked)) {
				$model = $this->exec($params);
			} else {
				$model = WNote::error('admin_no_access', "You don't have access to the administration of this application.");
			}
			
			// Config du template
			$this->configTheme();
			
			return $model;
		} else {
			return WNote::error('admin_access_forbidden', "You do not have access to the administration.");
		}
	}
	
	private function exec($params) {
		// Load admin controller of the app
		$app_dir = APPS_DIR.$this->appAsked.DS.'admin'.DS;
		$app_class = ucfirst($this->appAsked).'AdminController';
		
		if (file_exists($app_dir.'main.php')) {
			include_once $app_dir.'main.php';
		}
		
		if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
			$this->appController = new $app_class();
			
			// Instantiate Model if exists
			if (file_exists($app_dir.'model.php')) {
				include_once $app_dir.'model.php';
				$model_class = str_replace('Controller', 'Model', $app_class);
				if (class_exists($model_class)) {
					$this->appController->setModel(new $model_class());
				}
			}
			
			// Instantiate View if exists
			if (file_exists($app_dir.'view.php')) {
				include_once $app_dir.'view.php';
				$view_class = str_replace('Controller', 'View', $app_class);
				if (class_exists($view_class)) {
					$this->appController->setView(new $view_class());
				}
			}
			
			// Init
			$this->appController->init(array(
				'name'       => $this->appAsked,
				'directory'  => $app_dir,
				'controller' => $app_class,
				'admin'      => true
			));
			
			// Execute and get model
			$model = $this->appController->launch($params);
			
			// Linking the views
			$this->setView($this->appController->getView());
			
			// Update the action triggered
			$this->action = $this->appController->getTriggeredAction();
			
			return $model;
		} else {
			return WNote::error('app_structure', "No Admin implementation found for application \"".$this->appAsked."\".");
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
	 * Configurates the admin template
	 */
	private function configTheme() {
		// Config theme
		WConfig::set('config.theme', 'admin-bootstrap');
		
		// These are template variables => direct assign in WTemplate
		$tpl = WSystem::getTemplate();
		$tpl->assign('appsList', $this->getAdminApps());
		$tpl->assign('userNickname', $_SESSION['nickname']);
		
		if (!is_null($this->appController)) {
			$manifest = $this->appController->getManifest();
			$action_asked = $this->appController->getTriggeredAction();
			
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
