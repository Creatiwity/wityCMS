<?php
/**
 * WMain.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

require_once SYS_DIR.'WCore'.DS.'WController.php';
require_once SYS_DIR.'WCore'.DS.'WView.php';

/**
 * WMain is the main class that Wity launches at start-up.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class WMain {
	/**
	 * Initializes config, route, session, lang and then executes the application.
	 */
	public function __construct() {
		// Loading config
		$this->loadConfigs();

		// Initializing lang
		WLang::init();
		WLang::declareLangDir(SYS_DIR.'lang');

		// Initializing the route
		$this->route();

		// Initializing sessions
		$this->setupSession();

		// Setup Timezone
		$this->setupTimezone();

		// Setup Template
		$this->setupTemplate();

		// Initializing WRetrever
		WRetriever::init();

		// Executes the application
		$this->exec();
	}

	/**
	 * Executes the main application and wrap it into a response for the client.
	 * The default response is the view of the main application included into a theme.
	 *
	 * If the user adds /m/ in the beginning of the route, the response will be the serialized
	 * model of the application in a JSON structure for instance.
	 */
	private function exec() {
		// Get the application name
		$route = WRoute::route();

		$response = new WResponse();
		$model = WRetriever::getModel($route['app'], $route['params'], false);
		switch ($route['mode']) {
			case 'm': // Only model
				$response->renderModel($model);
				break;

			case 'v': // Only view
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$response->renderView($model, $view);
				break;

			case 'mv': // Model + View
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$response->renderModelView($model, $view);
				break;

			case 'o': // Only Model triggered and calculated but nothing returned
				break;

			default: // Render in a theme
				$view = WRetriever::getView($route['app'], $route['params'], false);
				$theme = ($route['admin']) ? 'admin-bootstrap': WConfig::get('config.theme');

				// Load language file from template
				if ($route['admin']) {
					WLang::declareLangDir(THEMES_DIR.'admin-bootstrap'.DS.'lang');
				}

				$response->render($view, $theme, $model);
				break;
		}
	}

	/**
	 * Loads WConfig
	 */
	private function loadConfigs() {
		WConfig::load('config', CONFIG_DIR.'config.php', 'php');

		// Init template handler
		WSystem::getTemplate();
		WTemplateCompiler::registerCompiler('config', array('WConfig', 'compile_config'));
	}

	/**
	 * Initializes the route.
	 * Prevents browser from trying to load a physical file.
	 */
	private function route() {
		WRoute::init();

		// Checks if the browser tried to load a physical file
		$error = false;
		$query = WRoute::getQuery();
		$length = strlen($query);
		if (substr($query, $length-3, 1) == '.') {
			$ext = substr($query, $length-2, 2);
			if ($ext == 'js') {
				$error = true;
			}
		} else if (substr($query, $length-4, 1) == '.') {
			$ext = substr($query, $length-3, 3);
			if (in_array($ext, array('js', 'css', 'png', 'jpg', 'gif', 'ico', 'svg', 'eot', 'ttf'))) {
				$error = true;
			}
		} else if (substr($query, $length-5, 1) == '.') {
			$ext = substr($query, $length-4, 4);
			if (in_array($ext, array('jpeg', 'woff'))) {
				$error = true;
			}
		}

		if ($error) {
			$route = WRoute::route();

			if ($route['app'] != 'media') {
				header('HTTP/1.0 404 Not Found');
				WNote::error(404, WLang::get('error_404'), 'die');
			}
		}
	}

	/**
	 * Initializes session and check the flood condition
	 */
	private function setupSession() {
		// Instanciates it
		$session = WSystem::getSession();

		// Anti-flood checking
		if (!$session->check_flood()) {
			$_POST = array();
		}

		// Variable for Roxy file manager
		$_SESSION['upload_dir'] = WRoute::getDir().'upload';

		// Set session lang
		if (!empty($_SESSION['lang'])) {
			WLang::setLang($_SESSION['lang']);
		}
	}

	/**
	 * Setup wityCMS timezone for dates
	 * Will change PHP and MySQL configuration
	 */
	private function setupTimezone() {
		// Get client timezone
		$timezone = WDate::getUserTimezone();

		// Define default GMT timezone if the Server's config is empty
		$server_timezone = ini_get('date.timezone');
		if (empty($server_timezone) || $server_timezone != $timezone->getName()) {
			date_default_timezone_set($timezone->getName());
		}

		// Calculates the offset to GMT in Hours
		$offset = $timezone->getOffset(new DateTime('now', new DateTimeZone('UTC')))/3600;
		$plus = ($offset >= 0) ? '+' : '';

		// Change MySQL timezone
		WSystem::getDB()->query("SET time_zone = '".$plus.$offset.":00'");
	}

	/**
	 * Template configuration with system variables.
	 */
	private function setupTemplate() {
		$tpl = WSystem::getTemplate();

		$route = WRoute::route();

		// Load language file from template
		WLang::declareLangDir(THEMES_DIR.WConfig::get('config.theme').DS.'lang');

		// Setup system template variables with $wity_ prefix
		$tpl_vars = array(
			'wity_base_url'         => WRoute::getBase(),
			'wity_site_title'       => WConfig::get('config.name'),
			'wity_page_title'       => WConfig::get('config.page_title'),
			'wity_page_description' => WConfig::get('config.page_description'),
			'wity_user'             => false,
			'wity_home'             => WRoute::getQuery() == '' || $route['app'] == WConfig::get('route.default_front'),
			'wity_app'              => $route['app'],
			'wity_query'            => WRoute::getQuery(),
			'wity_lang'             => WLang::getLang(),
			'wity_lang_iso'         => WLang::getLangISO(),
			'wity_site_favicon'     => WConfig::get('config.favicon'),
			'wity_site_icon'        => WConfig::get('config.icon')
		);

		if (WSession::isConnected()) {
			$tpl_vars['wity_user'] = true;
			$tpl_vars += array(
				'wity_user_nickname'  => $_SESSION['nickname'],
				'wity_user_email'     => $_SESSION['email'],
				'wity_user_groupe'    => $_SESSION['groupe'],
				'wity_user_firstname' => $_SESSION['firstname'],
				'wity_user_lastname'  => $_SESSION['lastname'],
				'wity_user_access'    => $_SESSION['access']
			);
		}

		$tpl->assign($tpl_vars, null, true);
	}
}

?>
