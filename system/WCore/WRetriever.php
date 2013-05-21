<?php 
/**
 * WRetriever.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WRetriever does...
 *
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.4-14-05-2013
 */
class WRetriever {
	private static $apps_list = array();
	
	/**
	 * @var Stores all the app already instantiated
	 * @static
	 */
	private static $controllers = array();
	
	public static function getModel($app_name, $params = array()) {
		// Get app controller
		$controller = self::getController($app_name, $params);
		
		// Input parameters in the app
		
		
		// Get model
		$model = array();
		if ($controller instanceof WController) {
			$model = $controller->launch();
		}
		
		// Apply some Dynamic Permissions on the model
		
		
		// Return the model
		return $model;
	}
	
	/**
	 * 
	 * 
	 * @return WView
	 */
	public static function getView($app_name, $view_name = '', $params = array(), $view_size = '') {
		// Get the model
		$model = self::getModel($app_name, $params);
		
		// Get app controller
		$controller = self::getController($app_name, $params);
		
		if ($controller instanceof WController) {
			$view = $controller->getView();
			
			if (empty($view_name)) {
				$view_name = $controller->getTriggeredAction();
			}
			
			// Prepare the view
			$view->prepare($view_name, $model);
			
			return $view;
		}
		
		return null;
	}
	
	/**
	 * If found, execute the application in the apps/$app_name directory
	 * 
	 * @param string $app_name name of the application that will be launched
	 * @return WController App Controller
	 */
	public static function getController($app_name, $params) {
		// Check if app not already instantiated
		if (isset(self::$controllers[$app_name])) {
			return self::$controllers[$app_name];
		}
		
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
				
				// Store the controller
				self::$controllers[$app_name] = $controller;
				
				return $controller;
			} else {
				WNote::error('app_structure', "The application \"".$app_name."\" has to have a main class inheriting from WController abstract class.", 'display');
			}
		} else {
			WNote::error(404, "The page requested was not found.", 'display');
		}
		return null;
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
				if (file_exists($appDir.DS.'front'.DS.'main.php')) {
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
}

?>