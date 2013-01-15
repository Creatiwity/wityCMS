<?php 
/**
 * WController.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WController is the base class that will be inherited by all the applications
 *
 * @package System\WCore
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
     * @var array Context of the application describing app's name, app's directory and app's main class
     */
	protected $context;
	
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
     * @param WMain  $wity     main Wity instance of WMain
     * @param array  $context  Context of the application describing app's name, app's directory and app's main class
     */
	public function init(WMain $wity, $context) {
		$this->wity = $wity;
		$this->context = $context;
		
		// Initialize view if the app's constructor did not do it
		if (is_null($this->view)) {
			$this->setView(new WView());
		}
		
		// Forward the context to the View
		$this->view->setContext($this->context);
		
		// Default theme configuration
		if ($this->view->getTheme() == '') {
			$this->view->setTheme(WConfig::get('config.theme'));
		}
		
		// Parse the manifest
		$this->loadManifest($this->getAppName());
		
		// Automaticly declare the language directory
		if (is_dir($context['directory'].DS.'lang')) {
			WLang::declareLangDir($context['directory'].DS.'lang');
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
			throw new Exception("The page \"".$action."\" cannot be found in application ".$this->getAppName().".");
		}
	}
	
    /**
     * Returns the application's name
     * 
     * @return string application's name
     */
	public function getAppName() {
		return $this->context['name'];
	}
	
	/**
     * Returns if the application is in admin mode or not
     * 
     * @return bool true if admin mode loaded, false otherwise
     */
	public function getAdminContext() {
		return $this->context['admin'];
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
	 * Load the manifest file of a given application
	 * 
	 * @param string $app_name name of the application owning the manifest
	 */
	public function loadManifest($app_name) {
		$manifest_href = APPS_DIR.$app_name.'/manifest.xml';
		if (file_exists($manifest_href)) {
			$xml = simplexml_load_file($manifest_href);
			$manifest = array();
			
			// Nodes to look for
			$nodes = array('title', 'version', 'date', 'icone', 'service', 'page', 'admin');
			foreach ($nodes as $node) {
				switch ($node) {
					case 'service':
						foreach ($xml->service as $page) {
							
						}
						break;
					
					case 'page':
						foreach ($xml->page as $page) {
							$attributes = $page->attributes();
							$manifest['admin'][] = array(
								'method' => $page->__toString(),
								'lang' => isset($attributes['lang']) ? $attributes['lang'] : '',
							);
						}
						break;
					
					case 'admin':
						foreach ($xml->admin->page as $page) {
							$attributes = $page->attributes();
							$manifest['admin'][] = array(
								'method' => $page->__toString(),
								'lang' => isset($attributes['lang']) ? $attributes['lang'] : '',
								'menu' => isset($attributes['menu']) ? $attributes['menu'] == 'true' : '',
							);
						}
						break;
					
					default:
						$manifest[$node] = property_exists($xml, $node) ? $xml->$node->__toString() : '';
						break;
				}
			}
			
			//var_dump($manifest);
		}
	}
}

?>