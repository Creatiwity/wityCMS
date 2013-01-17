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
	
	public function __construct() {
		// Change admin context
		$this->context['admin'] = true;
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
			
			if ($this->hasAccess($this->appAsked)) {
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
				WNote::error('admin_access_denied', "Vous n'avez pas accès à l'application <strong>".$this->appAsked."</strong> de l'administration.", 'display');
			}
		} else {
			WNote::error('admin_access_forbidden', "Vous n'avez pas accès à l'administration.", 'display');
		}
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
		if (!empty($app) && $this->isAdminApp($app)) {
			// Mise à jour du routage
			WRoute::updateApp($app);
			WRoute::updateArgs($args); // Nettoyage des arguments
		} else {
			$default = WConfig::get('route.admin');
			if ($this->hasAccess($default[0])) {
				WRoute::setRoute($default);
			} else {
				// Le user est forcément admin
				// On charge la première application à laquelle il a accès
				/*$cp = $_SESSION['access'];
				WRoute::setRoute(array(array_shift(array_keys($cp)), array()));*/
			}
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
	 * @return array
	 */
	public function getAdminAppList() {
		static $adminApps = array();
		if (empty($adminApps)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			foreach ($apps as $app) {
				$appName = strtolower(basename($app));
				if (file_exists($app.DS.'admin'.DS.'main.php') && $this->hasAccess($appName)) {
					$adminApps[] = $appName;
				}
			}
		}
		return $adminApps;
	}
	
	/**
	 * Une appli possède-t-elle une administration ?
	 * 
	 * @param $app string appName
	 * @return bool
	 */
	private function isAdminApp($app) {
		return in_array($app, $this->getAdminAppList());
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
		$tpl->assign('appList', $this->getAdminAppList());
		
		// Pseudonyme de l'utilisateur
		$tpl->assign('userNickname', $_SESSION['nickname']);
		
		// Aucune application n'a été chargée
		// On est donc sur la page d'accueil de l'admin
		if (!is_null($this->appController)) {
			$manifest = $this->appController->getManifest();
			$actionAsked = $this->appController->getAskedAction();
			
			$tpl->assign(array(
				'appSelected' => $this->appAsked,
				'actionsList' => $manifest['admin'],
				'actionAsked' => $actionAsked
			));
			$this->view->assign('page_title', sprintf('Administration &raquo; %s%s',
				ucwords($this->appAsked),
				isset($manifest['admin'][$actionAsked]) ? ' &raquo; '.$manifest['admin'][$actionAsked]['lang'] : ''
			));
		} else {
			$tpl->assign('appSelected', '');
		}
	}
}

?>
