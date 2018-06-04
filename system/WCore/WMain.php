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
 * @version 0.6.2-04-06-2018
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

		// Initializing request
		WRequest::init();

		// Initializing the route
		$this->setupRoute();

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

		// Load all langs in admin
		if ($route['admin'] && !empty($_SESSION['access'])) {
			$admin_apps = WController::getApps(true);

			foreach ($admin_apps as $admin_app_key => $admin_app) {
				WLang::declareLangDir(APPS_DIR.$admin_app_key.DS.'admin'.DS.'lang');
			}
		}

		$response = new WResponse();
		$model = WRetriever::getModel($route['url'], array(), false);
		switch ($route['mode']) {
			case 'm': // Only model
				$response->renderModel($model);
				break;

			case 'v': // Only view
				$view = WRetriever::getView($route['url'], array(), false);
				$response->renderView($model, $view);
				break;

			case 'mv': // Model + View
				$view = WRetriever::getView($route['url'], array(), false);
				$response->renderModelView($model, $view);
				break;

			case 'o': // Only Model triggered and calculated but nothing returned
				break;

			default: // Render in a theme
				$view = WRetriever::getView($route['url'], array(), false);

				if ($route['admin']) {
					$theme = WConfig::get('config.theme_admin');

					// Display login form if not connected
					if (!WSession::isConnected()) {
						$view = WRetriever::getView('user/login', array('redirect' => WRoute::getDir().'admin'));
					} else if (!empty($_SESSION['access'])) {
						$admin_apps = WController::getApps(true);

						$tpl = WSystem::getTemplate();
						$tpl->assign('wity_admin_apps', $admin_apps);
					}
				} else {
					$theme = WConfig::get('config.theme');
				}

				WLang::declareLangDir(THEMES_DIR.$theme.DS.'lang');

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
	private function setupRoute() {
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
		// Instantiate the Session
		$session = WSystem::getSession();

		// Anti-flood checking
		if (WConfig::get('config.anti_flood', false) && !$session->checkFlood()) {
			$_POST = array();
		}

		// Set Roxy file manager's upload dir variable
		$_SESSION['upload_dir'] = WRoute::getDir().'upload';

		// Set session lang
		if (empty($_SESSION['current_lang_code'])) {
			$lang = WLang::getDefaultLang();

			if (!empty($lang)) {
				$_SESSION['current_lang_code'] = $lang['code'];
				$_SESSION['current_lang_iso']  = $lang['iso'];
			} else {
				$_SESSION['current_lang_code'] = 'en_EN';
				$_SESSION['current_lang_iso']  = 'en';
			}
		}

		WLang::setLang($_SESSION['current_lang_code']);
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
		$front_route = WRoute::parseURL(WConfig::get('route.default_front'));

		// Setup system template variables with $wity_ prefix
		$tpl_vars = array(
			'wity_base_url'         => WRoute::getBase(),
			'wity_url'              => WRoute::getURL(),
			'wity_site_title'       => WConfig::get('config.site_title'),
			'wity_page_title'       => WConfig::get('config.page_title'),
			'wity_page_description' => WConfig::get('config.page_description'),
			'wity_user'             => false,
			'wity_home'             => WRoute::getQuery() == '' || WRoute::equals($route, $front_route),
			'wity_app'              => $route['app'],
			'wity_query'            => WRoute::getQuery(),
			'wity_lang'             => WLang::getLang(),
			'wity_lang_iso'         => WLang::getLangISO(),
			'wity_site_favicon'     => WConfig::get('config.favicon'),
			'wity_ga'               => WConfig::get('config.ga'),
			'wity_version'          => WConfig::get('config.version'),
			'wity_og_type'          => 'website',
			'wity_og_title'         => WConfig::get('config.og_title'),
			'wity_og_description'   => WConfig::get('config.og_description'),
			'wity_og_image'         => WConfig::get('config.og_image'),
			'wity_now'              => time()
		);

		if (WSession::isConnected()) {
			$tpl_vars['wity_user'] = true;
			$tpl_vars += array(
				'wity_userid'         => $_SESSION['userid'],
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
