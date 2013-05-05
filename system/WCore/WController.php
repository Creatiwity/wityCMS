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
 * @version 0.3-20-03-2013
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
			WNote::error('app_no_manifest', 'The manifest of the application '.$this->getAppName().' cannot be found');
		}
	}
	
	public function launch() {
		// Trigger proper method
		return $this->forward($this->getAskedAction());
	}
	
	/**
	 * Calls the application's method which is associated to the $action value
	 * 
	 * @param type $action  action under execution
	 * @param type $default optional default action value
	 * @return mixed Model|false if error
	 */
	protected final function forward($action) {
		if (!empty($action)) {
			if ($this->hasAccess($this->getAppName(), $action)) {
				// Execute action
				if (method_exists($this, $action)) {
					$this->action = $action;
					return $this->$action();
				} else {
					WNote::error('app_method_not_found', 'The method corresponding to the action "'.$action.'" cannot be found in '.$this->getAppName().' application.', 'display');
				}
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
	 * Returns action's name which is the second parameter given in the URL, right after the app's name.
	 * 
	 * If the $check param is set to true, the method will check if the action found is declared in 
	 * the manifest. If it is not declared, it will retun the default application action.
	 * If $check is set to false, it will return the action as it is asked in the URL.
	 * 
	 * @param bool $check Parameter to check if the action asked is defined (default to true)
	 * @return string action's name asked in the URL
	 */
	public function getAskedAction($check = true) {
		$args = WRoute::getArgs();
		$action = isset($args[0]) ? strtolower($args[0]) : '';
		
		// Find a fine $action
		if ($check) {
			if ($this->getAdminContext()) {
				$actions_key = 'admin';
				$alias_prefix = 'admin-';
				$default = 'default_admin';
			} else {
				$actions_key = 'actions';
				$alias_prefix = '';
				$default = 'default';
			}
			
			// $action exists ? Otherwise, check alias and finally, use default action if exists?
			if (!empty($action) && !isset($this->manifest[$actions_key][$action])) {
				// check alias
				if (isset($this->manifest['alias'][$alias_prefix.$action])) {
					$action = $this->manifest['alias'][$alias_prefix.$action];
				} else { // try to guess
					$action = str_replace('-', '_', $action);
					if (!isset($this->manifest[$actions_key][$action])) {
						$action = '';
					}
				}
			}
			if (empty($action) && isset($this->manifest[$default])) {
				$action = $this->manifest[$default];
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
			$manifest_file = APPS_DIR.$app_name.DS.'manifest.php';
			if (!file_exists($manifest_file)) {
				// WNote::error('controller_no_manifest', 'Unable to find the manifest "'.$manifest_file.'".', 'debug');
				return null;
			}
			
			// Checks cache directory
			if (!is_dir(CACHE_DIR.'manifests')) {
				@mkdir(CACHE_DIR.'manifests', 0777);
			}
			
			// Is there a manifest parsed in cache?
			$cache_file = CACHE_DIR.'manifests'.DS.$app_name.'.php';
			if (file_exists($cache_file) && @filemtime($cache_file) > @filemtime($manifest_file)) {
				include $cache_file;
			}
			if (!isset($manifest)) { // cache failed
				$manifest = $this->parseManifest($manifest_file);
				
				if (is_writable(CACHE_DIR.'manifests')) {
					// Opening
					if (!($handler = fopen($cache_file, 'w'))) {
						WNote::error('controller_create_manifest_cache', 'Unable to open the cache target "'.$cache_file.'".', 'debug');
					}
					
					// Writing
					fwrite($handler, "<?php\n\n\$manifest = ".var_export($manifest, true).";\n\n?>");
					fclose($handler);
				}
			}
			WConfig::set('manifest.'.$app_name, $manifest);
		}
		return $manifest;
	}
	
	/**
	 * Retrieves the manifest of the application running
	 * 
	 * @return array manifest
	 */
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
		
		$manifest_string = file_get_contents($manifest_href);
		$manifest_string = trim(preg_replace('#<\?php.+\?>#U', '', $manifest_string));
		
		$xml = simplexml_load_string($manifest_string);
		$manifest = array();
		
		// Nodes to look for
		$nodes = array('name', 'version', 'date', 'icone', 'action', 'admin', 'permission');
		foreach ($nodes as $node) {
			switch ($node) {
				case 'action':
					$manifest['actions'] = array();
					if (property_exists($xml, 'action')) {
						foreach ($xml->action as $action) {
							$attributes = $action->attributes();
							$key = strtolower((string) $action);
							if (!empty($key)) {
								if (!isset($manifest['actions'][$key])) {
									$manifest['actions'][$key] = array(
										'desc' => isset($attributes['desc']) ? (string) $attributes['desc'] : $key,
										'requires' => isset($attributes['requires']) ? array_map('trim', explode(',', $attributes['requires'])) : array()
									);
								}
								if (isset($attributes['default']) && empty($manifest['default'])) {
									$manifest['default'] = $key;
								}
								if (isset($attributes['alias']) && !empty($attributes['alias'])) {
									$alias = explode(',', $attributes['alias']);
									foreach ($alias as $al) {
										$al = strtolower(trim($al));
										if (!empty($al)) {
											$manifest['alias'][$al] = $key;
										}
									}
								}
							}
						}
					}
					break;
				
				case 'admin':
					$manifest['admin'] = array();
					if (property_exists($xml, 'admin') && property_exists($xml->admin, 'action')) {
						foreach ($xml->admin->action as $action) {
							if (!empty($action)) {
								$attributes = $action->attributes();
								$key = strtolower((string) $action);
								if (!empty($key)) {
									if (!isset($manifest['admin'][$key])) {
										$manifest['admin'][$key] = array(
											'desc' => isset($attributes['desc']) ? (string) $attributes['desc'] : $key,
											'menu' => isset($attributes['menu']) ? (string) $attributes['menu'] == 'true' : true,
											'requires' => isset($attributes['requires']) ? array_map('trim', explode(',', $attributes['requires'])) : array()
										);
									}
								}
								if (isset($attributes['default']) && empty($manifest['default_admin'])) {
									$manifest['default_admin'] = $key;
								}
								if (isset($attributes['alias']) && !empty($attributes['alias'])) {
									$alias = explode(',', $attributes['alias']);
									foreach ($alias as $al) {
										$al = strtolower(trim($al));
										if (!empty($al)) {
											$manifest['alias']['admin-'.$al] = $key;
										}
									}
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
				
				case 'name':
					$manifest['name'] = property_exists($xml, 'name') ? (string) $xml->name : basename(dirname($manifest_href));
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
		
		if ($admin) { // Admin mode ON
			if (empty($_SESSION['access'])) {
				return false;
			}
			if ($_SESSION['access'] == 'all') {
				return true;
			} else if (isset($_SESSION['access'][$app]) && is_array($_SESSION['access'][$app]) && in_array('admin', $_SESSION['access'][$app])) {
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
			} else if (isset($manifest['actions'][$action])) {
				// Check permissions
				foreach ($manifest['actions'][$action]['requires'] as $req) {
					switch ($req) {
						case 'not-connected':
							if (WSession::isConnected()) {
								WNote::error('app_logout_required', 'The '.$action.' action of the application '.$app.' requires to be loged out.', 'display');
								return false;
							}
							break;
						
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