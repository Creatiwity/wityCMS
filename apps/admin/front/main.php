<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/admin/front/main.php 0003 17-09-2011 Fofif $
 */

class AdminController extends WController {
	/**
	 * Instance de la classe Session
	 */
	private $session;
	/**
	 * Instance du modèle
	 */
	private $model;
	/**
	 * Nom de l'application à administrer
	 */
	private $appAsked;
	/**
	 * Instance du controller de l'application administrée
	 */
	private $appController = null;
	
	public function __construct() {
		$this->session = WSystem::getSession();
		
		// Inclusion du modèle
		include 'model.php';
		$this->model = new AdminModel();
	}
	
	/**
	 * Launcher de l'admin
	 * Vérifie si on y a accès, quelle app charger...
	 * 
	 * @return void
	 */
	public function launch() {
		if (!$this->session->isLoaded()) {
			// Affichage du formulaire d'autentification
			WNote::info('admin_login_required', "Cette zone nécessite une authentification.", 'assign');
			$this->wity->exec('user');
		} else if ($this->checkAdminAccess()) {
			$this->routeAdmin();
			
			$this->appAsked = WRoute::getApp();
			if ($this->isAdminApp($this->appAsked) && $this->checkAccess($this->appAsked)) {
				// Chargement de la partie admin de l'appli
				$app_dir = APPS_DIR.$this->appAsked.DS.'admin'.DS;
				include $app_dir.'main.php';
				$class = ucfirst($this->appAsked).'AdminController';
				$this->appController = new $class();
				$this->appController->init($this->wity, $app_dir);
				
				// Config du template
				$this->configTheme();
				
				// Execution
				$this->appController->launch();
			} else {
				// Config du template
				$this->configTheme();
				WNote::error('admin_access_denied', "Vous n'avez pas accès à la zone <strong>".$this->appAsked."</strong> de l'administration.", 'display');
			}
		} else {
			WNote::error('admin_access_forbidden', "Vous n'avez pas accès à l'administration.", 'display');
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
	 * Vérifie si l'utilisateur a accès à l'administration de l'app demandée
	 * 
	 * @param $app nom de l'appli
	 * @return bool
	 */
	public static function checkAccess($app) {
		return $_SESSION['accessString'] == 'all' || array_key_exists($app, $_SESSION['access']);
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
			if ($this->checkAccess($default[0]) && $this->isAdminApp($default[0])) {
				WRoute::setRoute($default);
			} else {
				// Le user est forcément admin
				// On charge la première application à laquelle il a accès
				$cp = $_SESSION['access'];
				WRoute::setRoute(array(array_shift(array_keys($cp)), array()));
			}
		}
	}
	
	/**
	 * Une appli possède-t-elle une administration ?
	 * 
	 * @param $app string appName
	 * @return bool
	 */
	private function isAdminApp($app) {
		return in_array($app, $this->model->getAdminAppList());
	}
	
	/**
	 * Traitement des actions d'une application
	 * Si la description d'une action débute par un antislash (\),
	 * elle ne doit pas s'afficher dans la liste des opérations
	 * 
	 * @param $actions Liste des actions
	 * @param $toDisplay bool détermine si les actions sont à afficher ou non
	 */
	private function treatActionList($actions, $toDisplay) {
		$final = array();
		foreach ($actions as $k => $v) {
			if ($toDisplay) {
				if (substr($v, 0, 1) != '\\') {
					$final[$k] = $v;
				}
			} else {
				$final[$k] = trim($v, '\\');
			}
		}
		return $final;
	}
	
	/**
	 * Configuration du thème de l'admin
	 * 
	 * @void
	 */
	private function configTheme() {
		// On fait coïncider les Views
		if (!is_null($this->appController)) {
			$this->setView($this->appController->getView());
		}
		
		// Configuration du thème
		WConfig::set('config.theme', 'admin');
		$this->view->setTheme('admin');
		
		// Ce sont des variables de template et non de view
		// Il est donc nécessaire des les assigner directement dans le moteur
		$tpl = WSystem::getTemplate();
		$tpl->assign('appList', $this->model->getAdminAppList());
		
		// Pseudonyme de l'utilisateur
		$tpl->assign('userNickname', $_SESSION['nickname']);
		
		// Aucune application n'a été chargée
		// On est donc sur la page d'accueil de l'admin
		if (!is_null($this->appController)) {
			$appActions = $this->appController->getActionList();
			$tpl->assign(array(
				'appSelected' => $this->appAsked,
				'actionList' => $this->treatActionList($appActions, false),
				'actionShownList' => $this->treatActionList($appActions, true),
			));
			$this->view->assign('page_title', sprintf('Administration &raquo; %s%s',
				ucwords($this->appAsked),
				isset($appActions[$this->appController->getAskedAction()]) ? ' &raquo; '.trim($appActions[$this->appController->getAskedAction()], '\\') : ''
			));
		} else {
			$tpl->assign('appSelected', '');
		}
	}
}

?>
