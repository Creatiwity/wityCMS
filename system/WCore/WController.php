<<<<<<< HEAD
<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: WCore/WController.php 0002 17-04-2011 Fofif $
 */

abstract class WController {
	// Instance de la classe WMain
	protected $wity;
	
	// Instance de la WView
	protected $view;
	
	// L'action de l'application
	protected $action = 'index';
	
	/**
	 * Initialisation de l'application
	 * 
	 * @param WMain $wity Instance de wity
	 */
	public function init(WMain $wity) {
		$this->wity = $wity;
		
		// Initialize view if the app's constructor did not dot it
		if (is_null($this->view)) {
			$this->view = new WView();
		}
	}
	
	/**
	 * Launcher de l'applcation exécuté par Wity
	 * 
	 * @abstract
	 */
	abstract public function launch();
	
	/**
	 * Retourne le nom de l'application
	 * 
	 * @return string
	 */
	public function getAppName() {
		$className = str_replace('_', '-', get_class($this));
		if (strpos($className, 'Admin') === 0) {
			$appName = 'admin';
		} else {
			$appName = strtolower(str_replace(array('AdminController', 'Controller'), '', $className));
		}
		return $appName;
	}
	
	/**
	 * Forward permet d'éxécuter la méthode associée à l'$action de l'application
	 * 
	 * @param string $action Action à exécuter
	 * @param string $default Action par défaut si $action est introuvable
	 */
	protected function forward($action, $default = '') {
		if (method_exists($this, $action)) {
			$this->action = $action;
			$this->$action();
		} else if (!empty($default)) {
			$this->forward($default);
		} else {
			throw new Exception("L'action \"".$action."\" est inconnue de l'application ".$this->getAppName().".");
		}
	}
	
	/**
	 * Retourne le nom de l'action initialement demandé en paramètre
	 * 
	 * @return string
	 */
	public function getAskedAction() {
		$args = WRoute::getArgs();
		if (isset($args[0])) {
			return $args[0];
		} else {
			return 'index';
		}
	}
	
	/**
	 * Retourne l'action effectivement déclenchée
	 */
	public function getTriggeredAction() {
		return $this->action;
	}
	
	/**
	 * Setter pour la view
	 */
	public function setView(WView $view) {
		unset($this->view);
		$this->view = $view;
	}
	
	/**
	 * Getter pour la view
	 * 
	 * @return WView
	 */
	public function getView() {
		return $this->view;
	}
	
	/**
	 * Récupère la liste des actions prédéfinies d'une app
	 */
	public function getActionList() {
		if (!empty($this->actionList) && is_array($this->actionList)) {
			return $this->actionList;
		} else {
			return array();
		}
	}
	
	/**
	 * Détermine si la partie admin est chargée ou non
	 * 
	 * @return bool
	 */
	public function adminLoaded() {
		return strpos(get_class($this), 'Admin') !== false;
	}
	
	/**
	 * Déclenche l'action d'affichage du template
	 */
	protected function render($action) {
		$this->view->assign('actionForwarded', $this->action);
		
		// View: find response and render it
		$action = str_replace(array('.html', '.tpl'), '', $action);
		$this->view->findResponse($this->getAppName(), $action, $this->adminLoaded());
		$this->view->render();
	}
}

?>
=======
<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: WCore/WController.php 0002 17-04-2011 Fofif $
 */

abstract class WController {
	/**
	 * WMain instance
	 */
	protected $wity;
	
	/**
	 * View object (of WView)
	 */
	protected $view;
	
	/**
	 * App's action to be performed
	 */
	private $action = 'index';
	
	/**
	 * Initialisation de l'application
	 * 
	 * @param WMain $wity Instance de wity
	 */
	public function init(WMain $wity) {
		$this->wity = $wity;
		
		// Initialize view if the app's constructor did not dot it
		if (is_null($this->view)) {
			$this->view = new WView();
		}
	}
	
	/**
	 * Launcher de l'applcation exécuté par Wity
	 * 
	 * @abstract
	 */
	abstract public function launch();
	
	/**
	 * Retourne le nom de l'application
	 * 
	 * @return string
	 */
	public function getAppName() {
		$className = str_replace('_', '-', get_class($this));
		if (strpos($className, 'Admin') === 0) {
			$appName = 'admin';
		} else {
			$appName = strtolower(str_replace(array('AdminController', 'Controller'), '', $className));
		}
		return $appName;
	}
	
	/**
	 * Forward permet d'éxécuter la méthode associée à l'$action de l'application
	 * 
	 * @param string $action Action à exécuter
	 * @param string $default Action par défaut si $action est introuvable
	 */
	protected function forward($action, $default = '') {
		if (method_exists($this, $action)) {
			$this->action = $action;
			$this->$action();
		} else if (!empty($default)) {
			$this->forward($default);
		} else {
			throw new Exception("L'action \"".$action."\" est inconnue de l'application ".$this->getAppName().".");
		}
	}
	
	/**
	 * Retourne le nom de l'action initialement demandé en paramètre
	 * 
	 * @return string
	 */
	public function getAskedAction() {
		$args = WRoute::getArgs();
		if (isset($args[0])) {
			return $args[0];
		} else {
			return 'index';
		}
	}
	
	/**
	 * Retourne l'action effectivement déclenchée
	 */
	public function getTriggeredAction() {
		return $this->action;
	}
	
	/**
	 * Setter pour la view
	 */
	public function setView(WView $view) {
		unset($this->view);
		$this->view = $view;
	}
	
	/**
	 * Getter pour la view
	 * 
	 * @return WView
	 */
	public function getView() {
		return $this->view;
	}
	
	/**
	 * Récupère la liste des actions prédéfinies d'une app
	 */
	public function getActionList() {
		if (!empty($this->actionList) && is_array($this->actionList)) {
			return $this->actionList;
		} else {
			return array();
		}
	}
	
	/**
	 * Détermine si la partie admin est chargée ou non
	 * 
	 * @return bool
	 */
	public function adminLoaded() {
		return strpos(get_class($this), 'Admin') !== false;
	}
	
	/**
	 * Déclenche l'action d'affichage du template
	 */
	protected function render($action) {
		$this->view->assign('actionForwarded', $this->action);
		
		// View: find response and render it
		$action = str_replace(array('.html', '.tpl'), '', $action);
		$this->view->findResponse($this->getAppName(), $action, $this->adminLoaded());
		$this->view->render();
	}
}

?>
>>>>>>> WTemplate + WLang
