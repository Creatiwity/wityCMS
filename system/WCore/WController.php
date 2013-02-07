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
	 * @var WMain main Wity instance of WMain 
	 */
	protected $wity;
	
	/**
	 * @var array Context of the application describing app's name, app's directory and app's main class
	 */
	protected $context;
	
	/**
	 * @var array Manifest of the application
	 */
	private $manifest;
	
	/**
	 * @var WView view object corresponding to this controller instance
	 */
	protected $view;
	
	/**
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
		if (is_dir($context['directory'].'lang')) {
			WLang::declareLangDir($context['directory'].'lang');
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
	 * @return boolean Action forwarding success
	 */
	protected function forward($action) {
		if (!empty($action)) {
			if ($this->hasAccess($this->getAppName(), $action)) {
				// Execute action
				if (method_exists($this, $action)) {
					$this->action = $action;
					$this->$action();
				} else {
					WNote::error('app_method_not_found', 'The method corresponding to the action "'.$action.'" cannot be found in '.$this->getAppName().' application.', 'display');
				}
				return true;
			} else {
				WNote::error('app_no_access', 'You do not have access to the application '.$this->getAppName().'.', 'display');
			}
		} else {
			WNote::error('app_no_suitable_action', 'No suitable action to trigger was found in the application '.$this->getAppName().'.', 'display');
		}
		return false;
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
		$action = isset($args[0]) ? $args[0] : '';
		
		// Find a fine $action
		if ($this->getAdminContext()) {
			// $action exists in admin ? Otherwise, default_admin action exists?
			if ((empty($action) || !isset($this->manifest['admin'][$action])) && isset($this->manifest['default_admin'])) {
				$action = $this->manifest['default_admin'];
			}
		} else {
			// $action exists ? Otherwise, default action exists?
			if ((empty($action) || !isset($this->manifest['pages'][$action])) && isset($this->manifest['default'])) {
				$action = $this->manifest['default'];
			}
		}
		return $action;
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
		$nodes = array('name', 'version', 'date', 'icone', 'service', 'page', 'admin', 'permission');
		foreach ($nodes as $node) {
			switch ($node) {
				case 'service':
					// foreach ($xml->service as $page) {
						
					// }
					break;
				
				case 'page':
					$manifest['pages'] = array();
					if (property_exists($xml, 'page')) {
						foreach ($xml->page as $page) {
							$attributes = $page->attributes();
							$key = (string) $page;
							if (!empty($key)) {
								if (!isset($manifest['pages'][$key])) {
									$manifest['pages'][$key] = array(
										'lang' => isset($attributes['lang']) ? (string) $attributes['lang'] : '',
										'requires' => isset($attributes['requires']) ? array_map('trim', explode(',', $attributes['requires'])) : array()
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
					$manifest['admin'] = array();
					if (property_exists($xml, 'admin') && property_exists($xml->admin, 'page')) {
						foreach ($xml->admin->page as $page) {
							if (!empty($page)) {
								$attributes = $page->attributes();
								$key = (string) $page;
								if (!empty($key)) {
									if (!isset($manifest['admin'][$key])) {
										$manifest['admin'][$key] = array(
											'lang' => isset($attributes['lang']) ? (string) $attributes['lang'] : '',
											'menu' => isset($attributes['menu']) ? (string) $attributes['menu'] == 'true' : true,
											'requires' => isset($attributes['requires']) ? array_map('trim', explode(',', $attributes['requires'])) : array()
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
				
				case 'permission':
					$manifest['permissions'] = !empty($manifest['admin']) ? array('admin') : array();
					if (property_exists($xml, 'permission')) {
						foreach ($xml->permission as $permission) {
							if (!empty($permission)) {
								$attributes = $permission->attributes();
								if (!empty($attributes['name'])) {
									$manifest['permissions'][] = (string) $attributes['name'];
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
	 * @param string  $app    Name of the app
	 * @param string  $action Action in the app to be checked (can be empty '' to check overall app access)
	 * @param boolean $admin  Admin context (default to Wity admin context)
	 * @return boolean
	 */
	public function hasAccess($app, $action = '', $admin = null) {
		if (is_null($admin)) {
			$admin = $this->getAdminContext();
		}
		
		// Check manifest
		$manifest = $this->loadManifest($app);
		if (is_null($manifest)) {
			return false;
		}
		
		// Administrator supreme case
		if (!empty($_SESSION['access']) && array_key_exists('all', $_SESSION['access'])) {
			return true;
		}
		
		if ($admin) { // Admin mode ON
			if (isset($_SESSION['access'][$app]) && in_array('admin', $_SESSION['access'][$app])) {
				if (empty($action)) { // Asking for application access
					return true;
				} else if (isset($manifest['admin'][$action])) {
					// Check permissions
					foreach ($manifest['admin'][$action]['requires'] as $req) {
						switch ($req) {
							case 'connected':
							case 'admin':
								break;
							
							default:
								if (!isset($_SESSION['access'][$app]) || !in_array($req, $_SESSION['access'][$app])) {
									WNote::error('app_no_access', 'You need more privileges to access the action '.$action.' in the application '.$app.'.', 'display');
									return false;
								}
								break;
						}
					}
					return true;
				}
			}
		} else { // Admin mode OFF
			if (empty($action)) { // Asking for application access
				return true;
			} else if (isset($manifest['pages'][$action])) {
				// Check permissions
				foreach ($manifest['pages'][$action]['requires'] as $req) {
					switch ($req) {
						case 'connected':
							if (!WSession::isConnected()) {
								WNote::error('app_login_required', 'The '.$action.' action of the application '.$app.' requires to be loged in.', 'display');
								return false;
							}
							break;
						
						default:
							if (!isset($_SESSION['access'][$app]) || !in_array($req, $_SESSION['access'][$app])) {
								WNote::error('app_no_access', 'You need more privileges to access the action '.$action.' in the application '.$app.'.', 'display');
								return false;
							}
							break;
					}
				}
				return true;
			}
		}
		return false;
	}
}

?>