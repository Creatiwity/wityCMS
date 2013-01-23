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
 * @version 0.3-17-01-2013
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
	 * @var array Manifest of the application
	 */
	private $manifest;
	
    /**
     *
     * @var WView view object corresponding to this controller instance
     */
	protected $view;
	
    /**
     *
     * @var string action that will be performed in this application (default: 'index')
     */
	private $action = '';
	
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
		
		// Automaticly declare the language directory
		if (is_dir($context['directory'].DS.'lang')) {
			WLang::declareLangDir($context['directory'].DS.'lang');
		}
		
		// Parse the manifest
		$this->manifest = $this->loadManifest($this->getAppName());
		if (empty($this->manifest)) {
			WNote::error('app_no_manifest', 'The manifest of the application '.$this->getAppName().' cannot be found', 'assign');
		}
	}
	
	public function launch() {
		// Trigger proper method
		$this->forward($this->getAskedAction());
	}
	
	/**
     * Calls the application's method which is associated to the $action value
     * 
     * @param type $action  action under execution
     * @param type $default optional default page value
     */
	protected function forward($action) {
		if (!empty($action) && $this->hasAccess($this->getAppName(), $action)) {
			$this->execAction($action);
		} else {
			if (!$this->getAdminContext() && isset($this->manifest['default'])) {
				$this->execAction($this->manifest['default']);
			} else if ($this->getAdminContext() && isset($this->manifest['default_admin'])) {
				$this->execAction($this->manifest['default_admin']);
			} else {
				if (empty($action)) {
					WNote::error('app_no_default_action', 'The application '.$this->getAppName().' has no default action.', 'display');
				} else {
					WNote::error('app_no_suitable_action', 'The application '.$this->getAppName().' does not know any action named '.$action.'.', 'display');
				}
			}
		}
	}
	
	/**
	 * Triggers the method corresponding to the action
	 * 
	 * @param string $action Name of the method to execute
	 * @throws Exception
	 */
	protected function execAction($action) {
		if (method_exists($this, $action)) {
			$this->action = $action;
			$this->$action();
		} else {
			WNote::error('app_method_not_found', 'The method corresponding to the action "'.$action.'" cannot be found in '.$this->getAppName().' application.', 'display');
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
     * Returns action's name which is the first parameter given in the URL, right after the app's name
     * 
     * @return string page's name asked in the URL
     */
	public function getAskedAction() {
		$args = WRoute::getArgs();
		if (isset($args[0])) {
			return $args[0];
		} else {
			return '';
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
	 * Loads the manifest file of a given application
	 * 
	 * @param string $app_name name of the application owning the manifest
	 * @return array manifest asked
	 */
	public function loadManifest($app_name) {
		$manifest = WConfig::get('manifest.'.$app_name);
		if (is_null($manifest)) {
			$manifest = $this->parseManifest(APPS_DIR.$app_name.DS.'manifest.xml');
			WConfig::set('manifest.'.$app_name, $manifest);
		}
		return $manifest;
	}
	
	public function getManifest() {
		return $this->manifest;
	}
	
	/**
	 * Parses a manifest file
	 * 
	 * @param string $manifest_href Href of the manifest file desired
	 * @return array manifest parsed into an array representation
	 */
	private function parseManifest($manifest_href) {
		if (!file_exists($manifest_href)) {
			return null;
		}
		
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
					if (property_exists($xml, 'page')) {
						foreach ($xml->page as $page) {
							$attributes = $page->attributes();
							$key = (string) $page;
							if (!empty($key)) {
								if (!isset($manifest['pages'][$key])) {
									$manifest['pages'][$key] = array(
										'lang' => isset($attributes['lang']) ? (string) $attributes['lang'] : '',
										'restriction' => isset($attributes['restriction']) ? intval($attributes['restriction']) : 0
									);
								}
								if (isset($attributes['default']) && empty($manifest['default'])) {
									$manifest['default'] = $key;
								}
							}
						}
					}
					break;
				
				case 'admin':
					if (property_exists($xml, 'admin') && property_exists($xml->admin, 'page')) {
						foreach ($xml->admin->page as $page) {
							if (!empty($page)) {
								$attributes = $page->attributes();
								$key = (string) $page;
								if (!empty($key)) {
									if (!isset($manifest['admin'][$key])) {
										$manifest['admin'][$key] = array(
											'lang' => isset($attributes['lang']) ? (string) $attributes['lang'] : '',
											'menu' => isset($attributes['menu']) ? (string) $attributes['menu'] == 'true' : true
										);
									}
								}
								if (isset($attributes['default']) && empty($manifest['default_admin'])) {
									$manifest['default_admin'] = $key;
								}
							}
						}
					}
					break;
				
				default:
					$manifest[$node] = property_exists($xml, $node) ? (string) $xml->$node : '';
					break;
			}
		}
		return $manifest;
	}
	
	/**
	 * Checks whether the user has access to an application, or a precise action of an application
	 * hasAccess('news') = does the user have access to news app?
	 * hasAccess('news', 'detail') = access to action detail in news app?
	 * 
	 * @param string $app    Name of the app
	 * @param string $action action in the app to be checked (can be empty '')
	 * @return boolean
	 */
	public function hasAccess($app, $action = '', $admin = false) {
		// Check manifest
		$manifest = $this->loadManifest($app);
		if (is_null($manifest)) {
			return false;
		}
		
		// Admin action?
		if ($this->getAdminContext()) {
			if (empty($action)) {
				// Asking for admin application access
				return in_array('all', $_SESSION['access']) || isset($_SESSION['access'][$app]);
			} else if (isset($manifest['admin'][$action])) {
				if (in_array('all', $_SESSION['access'])) {
					return true;
				} else if (isset($_SESSION['access'][$app])) {
					if ($_SESSION['access'][$app] === 0 || in_array($action, $_SESSION['access'][$app])) {
						return true;
					}
				}
			}
		} else {
			if (empty($action)) {
				// Asking for application access
				return true;
			} else if (isset($manifest['pages'][$action])) {
				switch ($manifest['pages'][$action]['restriction']) {
					case 0:
						return true;
					
					case 1:
						if (WSession::isConnected()) {
							return true;
						}
						break;
					
					case 2:
						if (in_array('all', $_SESSION['access'])) {
							return true;
						} else if (isset($_SESSION['access'][$app])) {
							if ($_SESSION['access'][$app] === 0 || in_array($action, $_SESSION['access'][$app])) {
								return true;
							}
						}
						break;
				}
			}
		}
		return false;
	}
}

?>