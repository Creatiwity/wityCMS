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
 * @version 0.5.0-11-02-2016
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
	 * @param string $app_name
	 * @param array  $params
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return array
	 */
	public static function getModel($app_name, array $params = array(), $has_parent = true) {
		// Get app controller
		$controller = self::getController($app_name, $has_parent);

		// Treat the GET querystring
		if (isset($params['querystring'])) {
			$querystring = explode('&', str_replace('&amp;', '&', $params['querystring']));
			foreach ($querystring as $assignment) {
				$data = addslashes($assignment);

				$equal_pos = strpos($data, '=');
				if ($equal_pos !== false) {
					// Extract key and value
					$key = substr($data, 0, $equal_pos);
					$value = substr($data, $equal_pos+1);

					// Update the Global variables
					WRequest::set($key, $value, "GET");
				}
			}
		}

		// Init model structure
		$model = array(
			'app-name'   => $app_name,
			'action'     => '',
			'params'     => $params,
			'has-parent' => $has_parent,
			'signature'  => '',
			'result'     => null,
			'headers'    => array()
		);

		// Get model
		if ($controller instanceof WController) {
			// Match the asked action with the manifest
			$action = $controller->getAskedAction($params);

			$model['signature'] = md5($app_name.$action.serialize($params));

			// Check if this model was not already calculated
			if (isset(self::$models[$model['signature']])) {
				return self::$models[$model['signature']];
			}

			// Lock access to the Request variables for non targeted apps
			$form_signature = WRequest::get('form_signature');
			$form_action = WRequest::get('form_action');

			if (!empty($form_action)) {
				// If form's action was specified, checks that it is equal to the current app
				$action_route = WRoute::parseURL($form_action);

				if ($action_route['app'] != $app_name || (isset($action_route['params'][0]) && $action_route['params'][0] != $action)) {
					WRequest::lock();
				}
			} else if (!empty($form_signature) && $form_signature != $model['signature']) {
				WRequest::lock();
			}

			// Trigger the action and get the result model
			$model['result'] = $controller->launch($action, $params);

			$model['action'] = $controller->getTriggeredAction();

			// Add headers to the model
			$model['headers'] = $controller->getHeaders();

			// Unlock the Request variables access
			WRequest::unlock();

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
	 * @param string $app_name   Application's name
	 * @param array  $params     Some special parameters to send to the controller (optional)
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return WView
	 */
	public static function getView($app_name, array $params = array(), $has_parent = true) {
		// Get app controller
		$controller = self::getController($app_name, $has_parent);

		if ($controller instanceof WController) {
			// Get the model
			$model = self::getModel($app_name, $params, $has_parent);

			if (is_array($model['result']) && array_keys($model['result']) == array('level', 'code', 'message', 'handlers')) {
				// If model is a Note
				$view = WNote::getView(array($model['result']));
			} else {
				$view = $controller->getView();

				$executable_action = preg_replace('#[^a-z_]#', '', $model['action']);

				// Prepare the view
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

	public static function getViewFromModel(array $model) {
		return self::getView($model['app-name'], $model['params'], $model['parent']);
	}

	/**
	 * If found, execute the application in the apps/$app_name directory
	 *
	 * @param string $app_code   Code of the application that will be launched: "admin/news" or "news"
	 * @param bool   $has_parent Defines if the app to retrieve is the main app (so does not have parent) or not
	 * @return WController App Controller
	 */
	public static function getController($app_code, $has_parent) {
		// Check if app not already instantiated
		if (isset(self::$controllers[$app_code])) {
			$context = self::$controllers[$app_code]->getContext();
			$context['parent'] = $has_parent;
			self::$controllers[$app_code]->setContext($context);

			return self::$controllers[$app_code];
		}

		// App asked exists?
		if (self::isApp($app_code)) {
			// For example, an admin application is: "admin/news"
			$admin = strpos($app_code, 'admin/') === 0;

			// Calculates app's directory and class name
			if ($admin) {
				$app_name  = substr($app_code, 6);
				$app_dir   = APPS_DIR.$app_name.DS.'admin'.DS;
				$app_name_clear = str_replace(' ', '', ucwords(preg_replace('#[^a-zA-Z]+#', ' ', $app_name)));
				$app_class = $app_name_clear.'AdminController';
			} else {
				$app_name  = $app_code;
				$app_dir   = APPS_DIR.$app_code.DS.'front'.DS;
				$app_name_clear = str_replace(' ', '', ucwords(preg_replace('#[^a-zA-Z]+#', ' ', $app_code)));
				$app_class = $app_name_clear.'Controller';
			}

			// Include the application Controller class
			include_once $app_dir.'main.php';

			// App's controller must inherit WController
			if (class_exists($app_class) && get_parent_class($app_class) == 'WController') {
				$context = array(
					'app-name'   => $app_name,
					'directory'  => $app_dir,
					'controller' => $app_class,
					'admin'      => $admin,
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
				self::$controllers[$app_code] = $controller;

				return $controller;
			} else {
				return WNote::error('app_structure', WLang::get('error_bad_app_structure', $app_code));
			}
		} else {
			return WNote::error(404, WLang::get('error_404'));
		}
	}

	/**
	 * Returns a list of applications that contains a main.php file in their front directory
	 *
	 * @return array Array of string containing app's name
	 */
	public static function getAppsList() {
		if (empty(self::$apps_list)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			foreach ($apps as $appDir) {
				if ($appDir != '.' && $appDir != '..') {
					// Check front
					if (file_exists($appDir.DS.'front'.DS.'main.php')) {
						self::$apps_list[] = basename($appDir);
					}

					// Check admin
					if (file_exists($appDir.DS.'admin'.DS.'main.php')) {
						self::$apps_list[] = 'admin/'.basename($appDir);
					}
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
			$args = explode('?', $args);
			$url = trim($args[0], '/');

			// Explode the route in several parts
			$custom_routes = WConfig::get('route.custom');
			if (isset($custom_routes[$url])) {
				$route = WRoute::parseURL($custom_routes[$url]);
			} else {
				$route = WRoute::parseURL($url);
			}

			if (!empty($route['app'])) {
				// Format the querystring PHP code if a querystring is given
				if (isset($args[1])) {
					$route['params']['querystring'] = $args[1];
				}

				$params_string = var_export($route['params'], true);

				// Replace all the template variables in the string
				$params_string = WTemplateParser::replaceNodes($params_string, create_function('$s', "return '\'.'.WTemplateCompiler::parseVar(\$s).'.\'';"));

				return 'WRetriever::getModel("'.$route['app'].'", '.$params_string.')';
			}
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
