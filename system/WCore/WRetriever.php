<?php
/**
 * WRetriever.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WRetriever is the component to get the model or the view of an action from any wityCMS's application.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WRetriever {
	/**
	 * @var Stores all the app already instantiated
	 * @static
	 */
	private static $controllers = array();

	/**
	 * @var Stores the models already calculated
	 * @static
	 */
	private static $models = array();

	public static function init() {
		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('retrieve_view', array('WRetriever', 'compile_retrieve_view'));
		WTemplateCompiler::registerCompiler('retrieve_model', array('WRetriever', 'compile_retrieve_model'));
	}

	/**
	 * Gets the model of an application/action
	 *
	 * @param string $url
	 * @param array  $extra_params
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return array
	 */
	public static function getModel($url, $extra_params = array(), $has_parent = true) {
		$route = WRoute::parseURL($url);
		$route['params'] = array_merge($route['params'], $extra_params);

		// Get app controller
		$controller = self::getController($url, $has_parent);

		// Treat the GET querystring
		if (!empty($route['querystring'])) {
			$querystring = explode('&', str_replace('&amp;', '&', $route['querystring']));
			foreach ($querystring as $assignment) {
				$data = addslashes($assignment);

				$equal_pos = strpos($data, '=');
				if ($equal_pos !== false) {
					// Extract key and value
					$key = substr($data, 0, $equal_pos);
					$value = substr($data, $equal_pos+1);

					// Update the Global variables
					WRequest::set($key, $value, 'GET');
				}
			}
		}

		// Init model structure
		$model = array(
			'url'        => $url,
			'app'        => $route['app'],
			'action'     => '',
			'parent'     => $has_parent,
			'signature'  => '',
			'result'     => null,
			'headers'    => array()
		);

		// Get model
		if ($controller instanceof WController) {
			$action_lower = strtolower($route['action']);

			// Match the asked action with the manifest
			$action = $controller->getExecutableAction($action_lower);

			// Push back action in params
			if ($action != $action_lower) {
				array_unshift($route['params'], $route['action']);
			}

			$model['signature'] = md5($url.serialize($extra_params));

			// Check if this model was not already calculated
			if (isset(self::$models[$model['signature']])) {
				return self::$models[$model['signature']];
			}

			// Trigger the action and get the result model
			$model['result'] = $controller->launch($action, $route['params']);

			$model['action'] = $controller->getExecutedAction();

			// Add headers to the model
			$model['headers'] = $controller->getHeaders();

			// Cache the value
			self::$models[$model['signature']] = $model;
		} else {
			$model['result'] = $controller;
		}

		return $model;
	}

	/**
	 * Gets the View of a given application/action
	 * The model will automatically be generated and the View will be prepared
	 * (the corresponding method to the action will be executed in WView)
	 *
	 * @param string $url
	 * @param array  $extra_params
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return WView
	 */
	public static function getView($url, $extra_params = array(), $has_parent = true) {
		// Get app controller
		$controller = self::getController($url, $has_parent);

		if ($controller instanceof WController) {
			// Get the model
			$model = self::getModel($url, $extra_params, $has_parent);

			if (is_array($model['result']) && array_keys($model['result']) == array('level', 'code', 'message', 'handlers')) {
				// If model is a Note
				$view = WNote::getView(array($model['result']));
			} else {
				$view = $controller->getView();

				// Prepare the view
				$executable_action = preg_replace('#[^a-z_]#', '', $model['action']);
				if (method_exists($view, $executable_action)) {
					$view->$executable_action($model['result']);
				}

				// Infers template file
				if ($view->getTemplate() == '') {
					$actionTemplateFile = $view->getContext('directory').'templates'.DS.$model['action'].'.html';
					if (file_exists($actionTemplateFile)) {
						$view->setTemplate($actionTemplateFile);
					}
				}

				// Update the context
				$view->setSignature($model['signature']);
			}

			return $view;
		} else {
			// Return a WView with error
			return WNote::getView(array($controller));
		}
	}

	/**
	 * Get the controller of an application for a given URL.
	 *
	 * @param string $url Url of the app to be launched
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return WController App Controller
	 */
	public static function getController($url, $has_parent = true) {
		$route = WRoute::parseURL($url);

		if (empty($route['app'])) {
			return null;
		}

		// Check if app not already instantiated
		if (isset(self::$controllers[$url])) {
			return self::$controllers[$url];
		}

		// Asked App exists?
		if (self::isApp($route['app'], $route['admin'])) {
			// Calculates app's directory and class name
			$app_name_clear = str_replace(' ', '', ucwords(preg_replace('#[^a-zA-Z]+#', ' ', $route['app'])));

			if ($route['admin']) {
				$app_dir   = APPS_DIR.$route['app'].DS.'admin'.DS;
				$app_class = $app_name_clear.'AdminController';
			} else {
				$app_dir   = APPS_DIR.$route['app'].DS.'front'.DS;
				$app_class = $app_name_clear.'Controller';
			}

			// Include the application Controller class
			include_once $app_dir.'main.php';

			// App's controller must inherit WController
			if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
				$context = array(
					'url'        => $url,
					'app'        => $route['app'],
					'directory'  => $app_dir,
					'controller' => $app_class,
					'admin'      => $route['admin'],
					'parent'     => $has_parent
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
				self::$controllers[$url] = $controller;

				return $controller;
			} else {
				WResponse::httpHeaderStatus(500);
				return WNote::error('app_structure', WLang::get('error_bad_app_structure', $route['app']));
			}
		} else {
			WResponse::httpHeaderStatus(404);
			return WNote::error(404, WLang::get('error_404'));
		}
	}

	/**
	 * Returns a list of applications that contains a main.php file in their front directory
	 *
	 * @return array Array of string containing app's name
	 */
	public static function getAppsList($admin = null) {
		static $all_apps = array();

		if (empty($all_apps)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);

			foreach ($apps as $appDir) {
				if ($appDir != '.' && $appDir != '..') {
					$all_apps[] = basename($appDir);
				}
			}
		}

		if (is_null($admin)) {
			return $all_apps;
		} else {
			$apps_list = array();

			foreach ($all_apps as $app) {
				if ($admin === true) {
					if (file_exists(APPS_DIR.$app.DS.'admin'.DS.'main.php')) {
						$apps_list[] = $app;
					}
				} else if ($admin === false) {
					if (file_exists(APPS_DIR.$app.DS.'front'.DS.'main.php')) {
						$apps_list[] = $app;
					}
				}
			}

			return $apps_list;
		}
	}

	/**
	 * Returns application existence
	 *
	 * @param string $app
	 * @param bool $admin Admin mode?
	 * @return bool true if $app exists, false otherwise
	 */
	public static function isApp($app, $admin = null) {
		return !empty($app) && in_array($app, self::getAppsList($admin));
	}

	/********************************************
	 * WTemplateCompiler's custom handlers part *
	 ********************************************/

	/**
	 * Handles the {retrieve_model} node in WTemplate
	 * {retrieve_model} will return an array of the targeted Model
	 *
	 * The full syntax is as follow:
	 * {retrieve_model app_name/action/param1/param2?var1=value1&var2=value2}
	 *
	 * Note that you can specify a querystring that will be accessible through WRequest.
	 *
	 * It should be used within a {set} node as follow:
	 * {set $model = {retrieve_model app_name/action/param1/param2?var1=value1&var2=value2}}
	 *
	 * @param string $args Model location + querystring: "app_name/action/param1/param2?var1=value1&var2=value2"
	 * @return string PHP string to trigger WRetriever that will return an array of the desired model
	 */
	public static function compile_retrieve_model($args) {
 		if (!empty($args)) {
 			// Replace all the template variables in the string
 			$args = WTemplateParser::replaceNodes($args, create_function('$s', "return '\".'.WTemplateCompiler::parseVar(\$s).'.\"';"));

 			$args = explode('?', $args);
 			$url = $args[0];

 			if (isset($args[1])) {
 				// Format the querystring PHP code if a querystring is given
 				$params_string = var_export($args[1], true);
 			} else {
 				$params_string = 'array()';
 			}

 			return 'WRetriever::getModel("'.$url.'", '.$params_string.')';
 		}

 		return '';
 	}

	/**
	 * Handles the {retrieve_view} node in WTemplate
	 * {retrieve_view} will return a compiled view of an internal or external application's action
	 *
	 * The full syntax is as follow :
	 * {retrieve_view app_name/action/param1/param2?var1=value1&var2=value2}
	 *
	 * Note that you can specify a querystring that will be accessible through WRequest.
	 *
	 * @param string $args View location + querystring: "app_name/action/param1/param2?var1=value1&var2=value2"
	 * @return string PHP string to fire the View compilation (will be replaced by the View HTML response in the end)
	 */
	public static function compile_retrieve_view($args) {
		// Use {retrieve_model} compiler
		$model_syntax = self::compile_retrieve_model($args);

		// Replace 'WRetriever::getModel' with 'WRetriever::getView'
		if (!empty($model_syntax)) {
			return '<?php echo '.str_replace('getModel', 'getView', $model_syntax).'->render(); ?>'."\n";
		}

		return '';
	}
}

?>
