<?php
/**
 * WSession.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WSession manages all session variables and anti flood system.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WSession {
	/*
	 * Default session life when the user asks to remember his account
	 * @type int
	 */
	const REMEMBER_TIME = 2419200; // 1 month

	/**
	 * Minimum time between two POST requests
	 */
	const FLOOD_TIME = 2;

	/**
	 * Time before the session expires (seconds)
	 */
	const TOKEN_EXPIRATION = 120;

	/*
	 * Maximum login attempts
	 */
	const MAX_LOGIN_ATTEMPT = 5;

	/**
	 * Return codes
	 */
	const LOGIN_SUCCESS = 0;
	const LOGIN_ERROR = 1;
	const LOGIN_MAX_ATTEMPT_REACHED = 2;

	/**
	 * Session setup
	 */
	public function __construct() {
		// No sid in HTML links
		ini_set('session.use_trans_sid', '0');
		session_name('wsid');
		session_set_cookie_params(self::REMEMBER_TIME, WRoute::getDir());

		// Start sessions
		session_start();

		if ($this->isConnected()) {
			// Token expiration checking
			if (empty($_SESSION['token_expiration']) || time() >= $_SESSION['token_expiration']) {
				$this->reloadSession($_SESSION['userid']);
			}
		}
		// Attempt to reload the user session based on its cookies
		else if (!empty($_COOKIE['userid'])) {
			// Hash => unique connection
			$this->reloadSession(intval($_COOKIE['userid']));
		}
	}

	/**
	 * Is the user logged in?
	 *
	 * @return boolean true if the user is logged in, false otherwise
	 */
	public static function isConnected() {
		return !empty($_SESSION['userid']);
	}

	/**
	 * Creates a session for the user
	 *
	 * @param string $nickname nickname
	 * @param string $password password
	 * @param int $remember Time in seconds to keep the session
	 * @return int State of the request (LOGIN_SUCCESS | 0 = error)
	 */
	public function createSession($nickname, $password, $remember = 0) {
		// In case of multiple errors of login, return an error
		// Stores in SESSION variable $login_try the login try number
		if (!isset($_SESSION['login_try']) || (isset($_SESSION['flood_time']) && $_SESSION['flood_time'] < time())) {
			$_SESSION['login_try'] = 0;
		} else if ($_SESSION['login_try'] >= self::MAX_LOGIN_ATTEMPT) {
			return self::LOGIN_MAX_ATTEMPT_REACHED;
		}

		// Treatment
		$nickname = trim($nickname);
		$password_hash = sha1(trim($password));

		// Search a matching couple (nickname, password_hash) in DB
		include_once APPS_DIR.'user'.DS.'front'.DS.'model.php';
		$userModel = new UserModel();
		$user = $userModel->matchUser($nickname, $password_hash);

		// User found
		if (!empty($user)) {
			unset($_SESSION['login_try']); // cleanup
			$this->setupSession($user);

			// Cookie setup
			$lifetime = $remember > 0 ? time() + $remember : 0;

			setcookie('userid', $_SESSION['userid'], $lifetime, WRoute::getDir());
			setcookie('hash', $this->generateHash($user['nickname'], $user['password']), $lifetime, WRoute::getDir());

			return self::LOGIN_SUCCESS;
		} else {
			// Attempt + 1
			$_SESSION['login_try']++;

			return self::LOGIN_ERROR;
		}
	}

	/**
	 * Setup session variables for the user
	 *
	 * @param string $userid current user id
	 * @param array $data data to store into $_SESSION
	 */
	private function setupSession($user) {
		$_SESSION['userid']        = $user['id'];
		$_SESSION['nickname']      = $user['nickname'];
		$_SESSION['email']         = $user['email'];
		$_SESSION['groupe']        = $user['groupe'];
		$_SESSION['firstname']     = $user['firstname'];
		$_SESSION['lastname']      = $user['lastname'];
		$_SESSION['lang_code']     = $user['lang'];
		$_SESSION['lang_iso']      = substr($user['lang'], 0, 2);
		$_SESSION['access_string'] = $user['access'];
		$_SESSION['access']        = $this->parseAccessString($user['access']);

		// Next checking time
		$_SESSION['token_expiration'] = time() + self::TOKEN_EXPIRATION;
	}

	/**
	 * Parses an access string of a user into an array.
	 *
	 * @param string $access_string
	 * @return array
	 */
	public static function parseAccessString($access_string) {
		if (empty($access_string)) {
			return '';
		} else if ($access_string == 'all') {
			return 'all';
		} else {
			$access = array();

			foreach (explode(',', $access_string) as $access_apps) {
				$first_bracket = strpos($access_apps, '[');
				if ($first_bracket !== false) {
					$app_name = substr($access_apps, 0, $first_bracket);
					$permissions = substr($access_apps, $first_bracket+1, -1);
					if (!empty($permissions)) {
						$access[$app_name] = explode('|', $permissions);
					}
				}
			}

			return $access;
		}
	}

	/**
	 * Disconnects the user
	 *
	 * @param bool $destroy Destroy the entire session (including other vars than user's)?
	 */
	public function closeSession($destroy = false) {
		// Delete vars
		unset(
			$_SESSION['userid'],
			$_SESSION['nickname'],
			$_SESSION['email'],
			$_SESSION['groupe'],
			$_SESSION['firstname'],
			$_SESSION['lastname'],
			$_SESSION['lang_code'],
			$_SESSION['lang_iso'],
			$_SESSION['access_string'],
			$_SESSION['access'],
			$_SESSION['token_expiration']
		);

		// Reset cookies
		setcookie('userid', '', time() - 3600, WRoute::getDir());
		setcookie('hash', '', time() - 3600, WRoute::getDir());

		if ($destroy) {
			$_SESSION = array();
			session_destroy();

			// Reset cookies
			setcookie(session_name(), '', time() - 3600, WRoute::getDir());
		}
	}

	/**
	 * Reloads a user based on cookies
	 *
	 * @param string $userid        current user id
	 * @param string $cookie_hash   cookie hash for security checking
	 * @return boolean true if successfully reloaded, false otherwise
	 */
	public function reloadSession($userid) {
		if (!empty($_COOKIE['hash'])) {
			include_once APPS_DIR.'user'.DS.'front'.DS.'model.php';
			$userModel = new UserModel();
			$user = $userModel->getUser($userid);

			if (!empty($user)) {
				// Check hash
				if ($_COOKIE['hash'] == $this->generateHash($user['nickname'], $user['password'])) {
					$this->setupSession($user);

					return true;
				}
			}
		}

		$this->closeSession();

		return false;
	}

	/**
	 * Generates a user-and-computer specific hash that will be stored in a cookie
	 *
	 * @param string $nick nickname
	 * @param string $pass password
	 * @param boolean $environment optional value: true if we want to use environment specific values to generate the hash
	 * @return string the generated hash
	 */
	public function generateHash($nick, $pass, $environment = true) {
		$string = $nick.$pass;

		// Link the hash to the user's environment
		if ($environment) {
			$string .= $_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_ACCEPT_LANGUAGE']."*";
		}

		return sha1($string);
	}

	/**
	 * Anti-flood method
	 *
	 * Checking the $_POST content to avoid multiple and repeating similar form submissions.
	 *
	 * @return boolean true if flood detected, false otherwise
	 */
	public function checkFlood() {
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$flood = true;

			// Referer checking
			if (empty($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
				header('location: '.WRoute::getBase());
				$flood = false;
			}
			// Last request checking
			else if (!empty($_SESSION['last_query']) && md5(serialize($_POST).serialize($_FILES)) == $_SESSION['last_query']) {
				WNote::info('flood_duplicate', WLang::get('info_flood_duplicate'));
				$flood = false;
			}
			// Flood time limit checking
			else if (empty($_SESSION['access'][0]) && !empty($_SESSION['flood_time']) && $_SESSION['flood_time'] > time()) {
				$exceptions = array('user');
				$route = WRoute::route();

				// Applications in $exceptions will bypass the flood checking
				if (!in_array($route['app'], $exceptions)) {
					WNote::info('flood_wait', WLang::get('info_flood_wait', self::FLOOD_TIME));
					$flood = false;
				}
			}

			// Updating flood variables
			$_SESSION['last_query'] = md5(serialize($_POST).serialize($_FILES));

			// Updating flood time at shutdown to let less priorized script using this variable
			register_shutdown_function(array($this, 'upgradeFlood'), time() + self::FLOOD_TIME + 1);

			return $flood;
		} else {
			// Creating SESSION variable $flood_time
			if (!isset($_SESSION['flood_time'])) {
				$_SESSION['flood_time'] = 0;
			}

			// Void last request
			$_SESSION['last_query'] = '';
		}

		return true;
	}

	/**
	 * Updates flood time.
	 *
	 * @param int $limit timestamp limit
	 */
	public function upgradeFlood($limit) {
		$_SESSION['flood_time'] = $limit;
	}

	/**
	 * Get the IP of the client.
	 *
	 * @return string Either an ipv4 or an ipv6 address
	 */
	public static function getIP() {
		if ($ip = $_SERVER['HTTP_CLIENT_IP']) {}
		else if ($ip = $_SERVER['HTTP_X_FORWARDED_FOR']) {}
		else if ($ip = $_SERVER['HTTP_X_FORWARDED']) {}
		else if ($ip = $_SERVER['HTTP_FORWARDED_FOR']) {}
		else if ($ip = $_SERVER['HTTP_FORWARDED']) {}
		else if ($ip = $_SERVER['HTTP_REMOTE_ADDR']) {}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * Checks if current session has access to an app and permission.
	 *
	 * @param string $app
	 * @param string $permission
	 * @return bool
	 */
	public static function hasPermission($app, $permission) {
		if (empty($_SESSION['access'])) {
			return false;
		}

		if ($_SESSION['access'] == 'all') {
			return true;
		}

		return isset($_SESSION['access'][$app]) && in_array($permission, $_SESSION['access'][$app]);
	}
}

?>
