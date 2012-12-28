<?php 
/**
 * WController.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WController is the base class that will be inherited by all the applications
 *
 * @package WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-28-09-2012
 */
abstract class WController {

    /**
     *
     * @var WMain main Wity instance of WMain 
     */
	protected $wity;
	
    /**
     *
     * @var string the full application directory
     */
	protected $app_dir;
	
    /**
     *
     * @var WView view object corresponding to this controller instance
     */
	protected $view;
	
    /**
     *
     * @var string action that will be performed in this application (default: 'index')
     */
	private $action = 'index';
	
    /**
     * Application initialization
     * 
     * @param WMain $wity main Wity instance of WMain
     * @param type $app_dir directory of the application inheriting this Controller
     */
	public function init(WMain $wity, $app_dir) {
		$this->wity = $wity;
		$this->app_dir = $app_dir;
		
		// Initialize view if the app's constructor did not do it
		if (is_null($this->view)) {
			$this->view = new WView();
		}
		
		// Default theme configuration
		if ($this->view->getTheme() == '') {
			$this->view->setTheme(WConfig::get('config.theme'));
		}
		
		// Parse the manifest
		
		
		// Automaticly declare the language directory
		if (is_dir($app_dir.DS.'lang')) {
			WLang::declareLangDir($app_dir.DS.'lang');
		}
	}
	
    /**
     * Application's launcher method
     */
	abstract public function launch();
	
    /**
     * Calls the application's method which is associated to the $action value
     * 
     * @param type $action action that we try to execute
     * @param type $default optional default action value
     * @throws Exception
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
     * Returns the application's name
     * 
     * @return string application's name
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
	 * Retourne le nom de l'action initialement demandé en paramètre
	 * 
	 * @return string
	 */
    /**
     * Returns the first asked action which was the first parameter of the forward method
     * 
     * @return string action name
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
     * Returns the real executed action
     * 
     * @return string real executed action name
     */
	public function getTriggeredAction() {
		return $this->action;
	}
	
    /**
     * Sets the private view property to $view
     * 
     * @param WView $view the view that will be associated to this instance of the controller
     */
	public function setView(WView $view) {
		unset($this->view);
		$this->view = $view;
	}
	
    /**
     * Returns the current view
     * 
     * @return WView the current view
     */
	public function getView() {
		return $this->view;
	}
	
    /**
     * Returns a list of available actions in this application
     * 
     * @return array(string) a list of available actions in this application
     */
	public function getActionList() {
		if (!empty($this->actionList) && is_array($this->actionList)) {
			return $this->actionList;
		} else {
			return array();
		}
	}
	
    /**
     * Returns if the application is in admin mode or not
     * 
     * @return bool true if admin mode loaded, false otherwise
     */
	public function adminLoaded() {
		return strpos(get_class($this), 'Admin') !== false;
	}
	
	/**
	 * Déclenche l'action d'affichage du template
	 */
    /**
     * Renders the application with the right view
     * 
     * @param string $action name of the file that will be used for rendering this application
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