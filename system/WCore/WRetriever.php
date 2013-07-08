<?php 
/**
 * WRetriever.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WRetriever does...
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4-14-06-2013
 */
class WRetriever {
	/**
	 * @var Stores the application list
	 * @static
	 */
	private static $apps_list = array();
	
	/**
	 * @var Stores all the app already instantiated
	 * @static
	 */
	private static $controllers = array();
	
	public static function init() {
		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('retrieve_view', array('WRetriever', 'compile_retrieve_view'));
	}
	
	/**
	 * Gets the model of an application/action
	 * 
	 * @param string $app_name
	 * @param array  $params
	 * @return array
	 */
	public static function getModel($app_name, array $params = array()) {
		// Get app controller
		$controller = self::getController($app_name);
		
		// Get model
		$model = array();
		if ($controller instanceof WController) {
			$return = $controller->launch($params);
			
			// Model must be an array
			if (!empty($return)) {
				if (is_array($return)) {
					$model = $return;
				} else {
					$model = array('result' => $return);
				}
			}
		}
		
		// Apply some Dynamic Permissions on the model
		
		
		// Return the model
		return $model;
	}
	
	/**
	 * Gets the View of a given application/action
	 * The model will automatically be generated and the View will be prepared
	 * (the corresponding method to the action will be executed in WView)
	 * 
	 * @param string $app_name  Application's name
	 * @param array  $params    Some special parameters to send to the controller (optional)
	 * @param string $view_size Size mode of the view expected (optional)
	 * @return WView
	 */
	public static function getView($app_name, array $params = array(), $view_size = '') {
		// Get app controller
		$controller = self::getController($app_name);
		
		if ($controller instanceof WController) {
			// Get the model
			$model = self::getModel($app_name, $params);
			
			if (array_keys($model) == array('level', 'code', 'message', 'handlers')) {
				// If model is a Note
				$view = WNote::getView(array($model));
			} else {
				$view = $controller->getView();
				
				// Get the real action triggered by the controller
				$triggered_action = $controller->getTriggeredAction();
				
				// Declare the template file
				$actionTemplateFile = $view->context['directory'].'templates'.DS.$triggered_action.'.html';
				if (file_exists($actionTemplateFile)) {
					$view->setTemplate($actionTemplateFile);
				}
				
				// Prepare the view
				if (method_exists($view, $triggered_action)) {
					$view->$triggered_action($model);
				}
				
				// @TODO: Check if template file is not empty
			}
			
			return $view;
		} else {
			// Return a WView with error
			return new WView();
		}
	}
	
	/**
	 * If found, execute the application in the apps/$app_name directory
	 * 
	 * @param string $app_name name of the application that will be launched
	 * @return WController App Controller
	 */
	public static function getController($app_name) {
		// Check if app not already instantiated
		if (isset(self::$controllers[$app_name])) {
			return self::$controllers[$app_name];
		}
		
		$controller = null;
		
		// App asked exists?
		if (self::isApp($app_name)) {
			// App controller file
			$app_dir = APPS_DIR.$app_name.DS.'front'.DS;
			include_once $app_dir.'main.php';
			$app_class = str_replace('-', '_', ucfirst($app_name)).'Controller';
			
			// App's controller must inherit WController
			if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
				$context = array(
					'name'       => $app_name,
					'directory'  => $app_dir,
					'controller' => $app_class,
					'admin'      => false
				);
				
				// Construct App Controller
				$controller = new $app_class();
				
				// Instantiate Model if exists
				if (file_exists($app_dir.'model.php')) {
					include_once $app_dir.'model.php';
					$model_class = str_replace('Controller', 'Model', $app_class);
					if (class_exists($model_class)) {
						$controller->setModel(new $model_class());
					}
				}
				
				// Instantiate View if exists
				if (file_exists($app_dir.'view.php')) {
					include_once $app_dir.'view.php';
					$view_class = str_replace('Controller', 'View', $app_class);
					if (class_exists($view_class)) {
						$controller->setView(new $view_class());
					}
				}
				
				// Init
				$controller->init($context);
			} else {
				WNote::error('app_structure', "The application \"".$app_name."\" has to have a main class inheriting from WController abstract class.", 'display');
			}
		} else {
			WNote::error(404, "The page requested was not found.", 'display');
		}
		
		// Store the controller
		self::$controllers[$app_name] = $controller;
		
		return $controller;
	}
	
	/**
	 * Returns a list of applications that contains a main.php file in their front directory
	 * 
	 * @return array(string)
	 */
	public static function getAppsList() {
		if (empty(self::$apps_list)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			foreach ($apps as $appDir) {
				if ($appDir != '.' && $appDir != '..' && file_exists($appDir.DS.'front'.DS.'main.php')) {
					self::$apps_list[] = basename($appDir);
				}
			}
		}
		return self::$apps_list;
	}
	
	/**
	 * Returns application existence
	 * 
	 * @param string $app
	 * @return bool true if $app exists, false otherwise
	 */
	public static function isApp($app) {
		return !empty($app) && in_array($app, self::getAppsList());
	}
	
	/*****************************************
	 * WTemplateCompiler's new handlers part *
	 *****************************************/
	/**
	 * Handles the {lang} node in WTemplate
	 * {lang} gives access to translation variables
	 * sprintf format (such as %s) may be use in language files like this :
	 * {lang index|{$arg1}} = sprintf(WLang::_('index'), {$arg1})
	 * 
	 * @param string $args language identifier if no closing node in template file
	 * @return string php string that calls the WLang::get()
	 */
	public static function compile_retrieve_view($args) {
		if (!empty($args)) {
			// Replace all the template variables in the string
			$args = WTemplateParser::replaceNodes($args, create_function('$s', "return '\".'.WTemplateCompiler::parseVar(\$s).'.\"';"));
			
			$args = explode('?', $args);
			
			// Explode the route in several parts
			$route = explode('/', $args[0]);
			
			if (count($route) >= 2) {
				// Extract the relevant data
				$app_name = addslashes(array_shift($route));
				$params = '';
				$vars_get = '';
				
				// Get the params from the route of the view
				foreach ($route as $part) {
					if (!empty($part)) {
						$params .= '"'.addslashes($part).'", ';
					}
					$params = substr($params, 0, -2);
				}
				
				// Extract the querystring
				if (isset($args[1])) {
					$querystring = explode('&', str_replace('&amp;', '&', $args[1]));
					foreach ($querystring as $key_value) {
						$data = explode('=', addslashes($key_value));
						if (count($data) == 2) {
							$vars_get .= '<?php WRequest::set("'.$data[0].'", "'.$data[1].'", "GET"); ?>'."\n";
						}
					}
				}
				
				return $vars_get.'<?php echo WRetriever::getView("'.$app_name.'", array('.$params.'))->render(); ?>'."\n";
			}
		}
		return '';
	}
}

?>