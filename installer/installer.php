<?php
/**
 * installer.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

require 'view.php';

/**
 * Installer installs Wity on the server (configuration files and MySQL tables).
 *
 * @package Installer
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class Installer {
	private $THEMES_DIR;
	private $APPS_DIR;
	private $CONFIG_DIR;

	private $EXCLUDED_THEMES = array('system', 'admin-bootstrap');
	private $EXCLUDED_APPS = array('admin');
	private $EXCLUDED_DIRS = array('.', '..');

	private $view;

	/**
	 * Security system
	 *
	 *  if (the lock file exists && the lock file is still valid) || lock file does not exist
	 *      create lock file (again)
	 *      execute control
	 *  else
	 *      return an error message (msg: delete lock file)
	 *
	 */
	public function launch() {
		$this->THEMES_DIR = "themes";
		$this->APPS_DIR = "apps";
		$this->CONFIG_DIR = "system".DS."config";

		$this->view = new View();

		$data = WRequest::getAssoc(array('command', 'installer', 'group'), '', 'POST');

		switch ($data['command']) {
			default:
			case 'START':
				$this->view->render();
				return;

			case 'INIT_INSTALLER':
				if (!class_exists('PDO')) {
					$this->view->error('installer', $data['installer'], 'System failure', 'PDO class cannot be found. This feature has been introduced since PHP5.1+');
				}
				break;

			case 'FINISH_INSTALLATION':
				// Store the data in config files
				if ($this->installerValidation($data)) {
					set_time_limit(0);

					// Get config data
					$config = WRequest::getAssoc(array('site_title', 'base', 'theme', 'language', 'timezone'), '', 'POST');
					$route = WRequest::getAssoc(array('front_app', 'admin_app'), '', 'POST');
					$database = WRequest::getAssoc(array('dbserver', 'dbport', 'dbuser', 'dbpassword', 'dbname', 'dbprefix'), '', 'POST');
					$user = WRequest::getAssoc(array('nickname', 'password', 'email', 'firstname', 'lastname'), '', 'POST');
					$user['password'] = sha1($user['password']);
					$user['confirm'] = 0;
					$user['access'] = 'all';
					$user['valid'] = 1;

					$database['dbprefix'] = (!empty($database['dbprefix'])) ? $database['dbprefix']."_":"";

					// Create SQL Tables
					$sql_commands = file_get_contents('installer'.DS.'bdd'.DS.'witycms.sql');
					$sql_commands = str_replace('prefix_', $database['dbprefix'], $sql_commands); // configure prefix
					$db = $this->getSQLServerConnection($database);
					$db->exec($sql_commands);
					$error = $db->errorInfo();
					if (!is_null($error[0]) && !$error[0]!=0) {
						$this->view->error('installer', $data['installer'], 'Fatal Error', 'Impossible to create the wityCMS tables in the database. Please, import installer/bdd/witycms.sql file manually in your database.');
						break;
					}

					// Save Database configuration
					WConfig::set('database.server', str_replace('localhost', '127.0.0.1', $database['dbserver']));
					WConfig::set('database.port', $database['dbport']);
					WConfig::set('database.user', $database['dbuser']);
					WConfig::set('database.pw', $database['dbpassword']);
					WConfig::set('database.dbname', $database['dbname']);
					WConfig::set('database.prefix', $database['dbprefix']);
					WConfig::save('database', CONFIG_DIR.'database.php');

					// Create user account
					$placeholder = false;
					if ($this->isFrontApp('user', null, $placeholder)) {
						include APPS_DIR.'user'.DS.'admin'.DS.'model.php';
						$userModel = new UserAdminModel();
						if (!$userModel->createUser($user)) {
							$this->view->error('installer', $data['installer'], 'Fatal Error', 'Impossible to create your administrator account. Please, check your database credentials.');
							break;
						}
					} else {
						$this->view->error('installer', $data['installer'], 'Fatal Error', 'The User application required by the system cannot be found. Please, download a complete package of wityCMS.');
						break;
					}

					// Save General configuration
					WConfig::set('config.base', trim($config['base'], '/'));
					WConfig::set('config.site_title', $config['site_title']);
					WConfig::set('config.page_title', $config['site_title']);
					WConfig::set('config.page_description', '');
					WConfig::set('config.theme', $config['theme']);
					WConfig::set('config.theme_admin', 'admin-bootstrap');
					WConfig::set('config.lang', $config['language']);
					WConfig::set('config.timezone', $config['timezone']);
					WConfig::set('config.email', $user['email']);
					WConfig::set('config.debug', false);
					WConfig::set('config.anti_flood', true);
					WConfig::set('config.version', '1.0.0');
					WConfig::save('config', CONFIG_DIR.'config.php');

					// Save Route configuration
					WConfig::set('route.default_front', $route['front_app']);
					WConfig::set('route.default_admin', $route['admin_app']);
					WConfig::save('route', CONFIG_DIR.'route.php');

					// If success, attempt to delete installer directory
					if (file_exists(CONFIG_DIR.'config.php') && file_exists(CONFIG_DIR.'database.php') && file_exists(CONFIG_DIR.'route.php')) {
						$dir = WITY_PATH.'installer';
						$it = new RecursiveDirectoryIterator($dir);
						$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
						foreach ($files as $file) {
							if ($file->getFilename() === '.' || $file->getFilename() === '..') {
								continue;
							}

							if ($file->isDir()) {
								@rmdir($file->getRealPath());
							} else {
								@unlink($file->getRealPath());
							}
						}

						@rmdir($dir);

						$this->view->success('installer', $data['installer'], 'Congratulations', 'Installation finished !');
					} else {
						$this->view->error('installer', $data['installer'], 'Fatal Error', 'The installation did not complete successfully. Please, set the chmod of the directory "system/config/" to 0775 and rerun the installation again.');
						break;
					}
				} else {
					$this->view->error('installer', $data['installer'], 'Fatal Error', 'Data submitted cannot be validated. Please, restart the installation and fill in the form again.');
					break;
				}
				break;

			// Remote
			case 'REMOTE_VALIDATION':
				$this->remoteValidation($data);
				break;

			// Autocompletes
			case 'GET_THEMES':
				if ($themes = $this->toOptions($this->getThemes())) {
					$this->view->push_content("GET_THEMES", $themes);
				} else {
					$this->view->error('installer', $data['installer'], 'Fatal Error', 'Themes directory cannot be found.');
				}
				break;

			case 'GET_FRONT_APPS':
				if ($themes = $this->toOptions($this->getFrontApps())) {
					$this->view->push_content("GET_FRONT_APPS", $themes);
				} else {
					$this->view->error('installer', $data['installer'], 'Fatal Error', 'Applications directory cannot be found.');
				}
				break;

			case 'GET_ADMIN_APPS':
				if ($themes = $this->toOptions($this->getAdminApps())) {
					$this->view->push_content("GET_ADMIN_APPS", $themes);
				} else {
					$this->view->error('installer', $data['installer'], 'Fatal Error', 'Applications directory cannot be found.');
				}
				break;
		}

		$this->view->respond();
	}

	/**
	 * Checks that every field sent is validated
	 *
	 * @param array $data
	 * @return bool
	 */
	private function installerValidation($data) {
		$inputs = array('site_title', 'base_url', 'theme', 'language', 'front_app', 'admin_app', 'db_credentials', 'db_name', 'tables_prefix', 'user_password', 'user_nickname', 'user_email');
		foreach ($inputs as $input_name) {
			$data['group'] = $input_name;
			if (!$this->remoteValidation($data)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Remote validator
	 *
	 * @param array $data Contains the name of the validator
	 * @return bool
	 */
	private function remoteValidation($data) {
		$respond = true;

		switch ($data['group']) {
			case 'site_title':
				$site_title = WRequest::get('site_title', '', 'POST');
				if ($this->isVerifiedString($site_title, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Site name validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid site name", "The site name must be an alphanumeric string. (- and ' and spaces are allowed too)");
					return false;
				}

			case 'base_url':
				$base_url = WRequest::get('base', '', 'POST');
				if ($this->isURL($base_url, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Base URL validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid base url", "The base url must be a valid URL representing the constant part of your site URL.");
					return false;
				}

			case 'theme':
				$theme = WRequest::get('theme', '', 'POST');
				if ($this->isTheme($theme, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Theme validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid theme", "Theme parameter must be an existing front theme, in 'themes' directory.");
					return false;
				}

			case 'language':
				// TODO : auto-detect available languages and validate them
				$this->view->success('group', $data['group'], "Validated !", "Language validated.");
				return true;

			case 'timezone':
				// TODO : auto-detect available languages and validate them
				$this->view->success('group', $data['group'], "Validated !", "Timezone validated.");
				return true;

			case 'front_app':
				$front_app = WRequest::get('front_app', '', 'POST');
				if ($this->isFrontApp($front_app, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Front application validated.");
					return true;
				} else if ($respond && $front_app != 'Select') {
					$this->view->error('group', $data['group'], "Invalid front application", "Starting front application parameter must an existing front application, in 'apps' directory.");
					return false;
				}
				break;

			case 'admin_app':
				$admin_app = WRequest::get('admin_app', '', 'POST');
				if ($this->isAdminApp($admin_app, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Admin application validated.");
					return true;
				} else if ($respond && $admin_app != 'Select') {
					$this->view->error('group', $data['group'], "Invalid admin application", "Starting admin application parameter must an existing admin application, in 'apps' directory.");
					return false;
				}
				break;

			case 'db_credentials':
				$r = WRequest::getAssoc(array('dbserver', 'dbport', 'dbuser', 'dbpassword'), '', 'POST');
				if ($this->isSQLServer($r, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Database credentials validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid database credentials", "Unable to connect to the database with the credentials you've just provided.");
					return false;
				}

			case 'db_name':
				$r = WRequest::getAssoc(array('dbserver', 'dbport', 'dbuser', 'dbpassword', 'dbname'), '', 'POST');
				if ($this->isDatabase($r, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Database name validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid database name", "Unable to find the database with the name you've just provided.");
					return false;
				}

			case 'tables_prefix':
				$r = WRequest::getAssoc(array('dbserver', 'dbport', 'dbuser', 'dbpassword', 'dbname', 'dbprefix'), '', 'POST');
				if ($this->isPrefixNotExisting($r, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Tables prefix validated and not used.");
				} else if ($respond) {
					$this->view->warning('group', $data['group'], "Prefix already used", "Be careful, the prefix you provides is already used. Some existing tables will be overridden");

				}
				return true;

			case 'user_nickname':
				$user_nickname = WRequest::get('nickname', '', 'POST');
				if ($this->isVerifiedString($user_nickname, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Nickname validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid nickname", "Your nickname must be an alphanumeric string. (- and ' and spaces are allowed too)");
					return false;
				}

			case 'user_password':
				$user_password = WRequest::get(array('password', 'confirm'), '', 'POST');
				$this->view->success('group', $data['group'], "Validated !", "Password validated.");
				return true;

			case 'user_email':
				$user_email = WRequest::get('email', '', 'POST');
				if ($this->isEmail($user_email, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Email validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid email", "This email is not valid.");
					return false;
				}

			case 'user_firstname':
				$user_firstname = WRequest::get('firstname', '', 'POST');
				if ($this->isVerifiedString($user_firstname, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Firstname validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid firstname", "Your firstname must be an alphanumeric string. (- and ' and spaces are allowed too)");
					return false;
				}

			case 'user_lastname':
				$user_lastname = WRequest::get('lastname', '', 'POST');
				if ($this->isVerifiedString($user_lastname, $data, $respond)) {
					$this->view->success('group', $data['group'], "Validated !", "Lastname validated.");
					return true;
				} else if ($respond) {
					$this->view->error('group', $data['group'], "Invalid lastname", "Your lastname must be an alphanumeric string. (- and ' and spaces are allowed too)");
					return false;
				}

			default:
				$this->view->error('installer', $data['installer'], 'Unknown group', "You're trying to validate an unknown group.");
				return false;
		}
	}

	/**
	 * URL Validator
	 * Checks that a string is a URL where wityCMS can be installed
	 *
	 * @param string $url
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isURL($url, $data, &$respond) {
		return !empty($url) && preg_match("#^(http|https|ftp)\://[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(:[0-9]+)?(/[A-Z0-9~\._-]+)*/?$#i", $url);
	}

	/**
	 * URL Validator
	 * Checks that a string is ...
	 *
	 * @param string $string
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isVerifiedString($string, $data, &$respond) {
		return empty($url) || (!empty($url) && preg_match("/^[A-Z]?'?[- a-zA-Z]( [a-zA-Z])*$/i", $string));
	}

	/**
	 * Front App Validator
	 * Checks that a string corresponds to an existing Front Application
	 *
	 * @param string $app Front application name
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isFrontApp($app, $data, &$respond) {
		return in_array(strtolower($app), $this->getFrontApps());
	}

	/**
	 * Admin App Validator
	 * Checks that a string corresponds to an existing Admin Application
	 *
	 * @param string $app Admin application name
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isAdminApp($app, $data, &$respond) {
		return in_array(strtolower($app), $this->getAdminApps());
	}

	/**
	 * Theme Validator
	 * Checks that a string corresponds to an existing Theme
	 *
	 * @param string $theme Theme name
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isTheme($theme, $data, &$respond) {
		return in_array(strtolower($theme), $this->getThemes());
	}

	/**
	 * Create the PDO Object to connect to the database
	 *
	 * @param array $credentials array(server, port, dbname, user, pw)
	 * @return mixed true|PDOException
	 */
	private function getSQLServerConnection($credentials) {
		// Build dsn
		$dsn = 'mysql:dbname=';
		if (!empty($credentials['dbname'])) {
			$dsn .= $credentials['dbname'];
		}
		$dsn .= ';host='.$credentials['dbserver'].';';
		if (!empty($credentials['dbport']) && is_numeric($credentials['dbport'])) {
			$dsn .=  'port='.$credentials['dbport'];
		}

		try {
			return @new PDO($dsn, $credentials['dbuser'], $credentials['dbpassword']);
		} catch (PDOException $e) {
			return $e;
		}
	}

	/**
	 * Checks the SQL server credentials
	 *
	 * @param array $credentials MySQL Database credentials
	 * @param array $data
	 * @param bool  $respond
	 * @return bool
	 */
	private function isSQLServer($credentials, $data, &$respond) {
		$db = $this->getSQLServerConnection($credentials);

		if ($db instanceof PDOException && strstr($db->getMessage(), 'SQLSTATE[')) {
			preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $db->getMessage(), $matches);
			if ($matches[2] == "1049") {
				return true;
			} else if ($matches[2] == "1044") {
				$this->view->error('group', $data['group'], 'Unable to connect to the database', "Bad user/password.");
				return $respond = false;
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks that a specified database exists on the SQL Server
	 *
	 * @param array $credentials MySQL Database credentials
	 * @param array $data
	 * @param bool  $respond
	 * @return bool
	 */
	private function isDatabase($credentials, $data, &$respond) {
		$db = $this->getSQLServerConnection($credentials);

		if ($db instanceof PDOException && strstr($db->getMessage(), 'SQLSTATE[')) {
			preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $db->getMessage(), $matches);
			if ($matches[2] == "1049") {
				$this->view->error('group', $data['group'], 'Unable to find the database', "The database you specified cannot be found.");
				return $respond = false;
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks that a prefix is available in the database
	 *
	 * @param array $credentials MySQL Database credentials
	 * @param array $data
	 * @param bool  $respond
	 * @return bool
	 */
	private function isPrefixNotExisting($credentials, $data, &$respond) {
		$db = $this->getSQLServerConnection($credentials);

		if ($db instanceof PDO) {
			$prefix = (!empty($credentials['dbprefix'])) ? $credentials['dbprefix']."_":"";
			$prefix .= "users";

			$prep = $db->prepare("SHOW TABLES LIKE :prefixedTable");
			$prep->bindParam(":prefixedTable", $prefix);
			$prep->execute();
			$data = $prep->fetch();
			return empty($data);
		}

		return false;
	}

	/**
	 * Email Validator
	 * Checks that a string corresponds to an email
	 *
	 * @param string $email Email address
	 * @param array $data
	 * @param $respond
	 * @return bool
	 */
	private function isEmail($email, $data, &$respond) {
		return !empty($email) && preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email);
	}

	/**
	 * Get existing themes
	 *
	 * @return array List of themes
	 */
	private function getThemes() {
		if ($themes = scandir($this->THEMES_DIR)) {
			foreach ($themes as $key => $value) {
				if (in_array($value, $this->EXCLUDED_THEMES) || !is_dir($this->THEMES_DIR.DS.$value) || in_array($value, $this->EXCLUDED_DIRS)) {
					unset($themes[$key]);
				}
			}
			$themes[] = "_blank";
		}

		return $themes;
	}

	/**
	 * Get existing Front Apps
	 *
	 * @return array List of Front Apps
	 */
	private function getFrontApps() {
		$apps = array('select' => 'Select');

		if ($scanned_apps = scandir($this->APPS_DIR)) {
			foreach ($scanned_apps as $key => $value) {
				if (!in_array($value, $this->EXCLUDED_APPS) && is_dir($this->APPS_DIR.DS.$value.DS."front") && !in_array($value, $this->EXCLUDED_DIRS)) {
					$apps[$key] = $value;
				}
			}
		}

		return $apps;
	}

	/**
	 * Get existing Admin Apps
	 *
	 * @return array List of Admin Apps
	 */
	private function getAdminApps() {
		$apps = array('select' => 'Select');

		if ($scanned_apps = scandir($this->APPS_DIR)) {
			foreach ($scanned_apps as $key => $value) {
				if (!in_array($value, $this->EXCLUDED_APPS) && is_dir($this->APPS_DIR.DS.$value.DS."admin") && !in_array($value, $this->EXCLUDED_DIRS)) {
					$apps[$key] = 'admin/'.$value;
				}
			}
		}

		return $apps;
	}

	/**
	 * Transform an array of possibilities into an html-option list
	 *
	 * @param array
	 * @return string <option></option> string
	 */
	private function toOptions($optArray) {
		$result = '';

		if ($optArray) {
			foreach ($optArray as $value) {
				$result .= '<option value="'.$value.'">'.$value.'</option>';
			}

			return $result;
		} else {
			return false;
		}
	}
}

?>
