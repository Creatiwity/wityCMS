<?php
/**
 * User Application - Controller - /apps/user/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserController is the front Controller of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-06-03-2013
 */
class UserController extends WController {
	/*
	 * Default session life when the user asks to remember his account = 1 week
	 */
	const REMEMBER_TIME = 604800;
	
	/*
	 * @var Instance of WSession
	 */
	private $session;
	
	/**
	 * @var Instance of UserModel
	 */
	private $model;
	
	/**
	 * UserController constructor
	 * Basically instantiates vars: model, views and session
	 */
	public function __construct() {
		include_once 'model.php';
		$this->model = new UserModel();
		
		include_once 'view.php';
		$this->setView(new UserView($this->model));
		
		$this->session = WSystem::getSession();
	}
	
	/**
	 * Login action handler
	 * Triggered whenever a user asks to be connected
	 * 
	 * @param string $redirect URL to redirect the request
	 */
	protected function login($redirect = '') {
		// Find redirect URL
		$referer = WRoute::getReferer();
		$redirect_request = WRequest::get('redirect');
		if (empty($redirect)) {
			if (!empty($redirect_request)) {
				$redirect = $redirect_request;
			} else if (WRoute::getApp() != 'user') { // Login form loaded from an external application
				$redirect = WRoute::getURL();
			} else if (strpos($referer, 'user') === false) {
				$redirect = $referer;
			} else {
				$redirect = WRoute::getBase();
			}
		}
		
		if ($this->session->isConnected()) {
			// WNote::error('user_already_connected', 'No need to access to the login form since you are already connected.', 'display');
			header('location: '.$redirect);
			return;
		}
		
		// Vars given to trigger login process?
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'remember', 'time'));
			$cookie = true; // cookies accepted by browser?
			if (!empty($data['nickname']) && !empty($data['password'])) {
				// User asks to be auto loged in => change the cookie lifetime to self::REMEMBER_TIME
				$remember_time = !empty($data['remember']) ? self::REMEMBER_TIME : abs(intval($data['time'])) * 60;
				
				// Start login process
				switch ($this->session->createSession($data['nickname'], $data['password'], $remember_time)) {
					case WSession::LOGIN_SUCCESS:
						// Update activity
						$this->model->updateLastActivity($_SESSION['userid']);
						
						if (empty($_COOKIE['wsid'])) {
							WNote::info('user_cookie_not_accepted', WLang::get('cookie_not_accepted'));
							$cookie = false;
						} else {
							// Redirect
							WNote::success('user_login_success', WLang::get('login_success', $_SESSION['nickname']));
							header('location: '.$redirect);
							return;
						}
						break;
					
					case WSession::LOGIN_MAX_ATTEMPT_REACHED:
						WNote::error('user_login_max_attempt', WLang::get('login_max_attempt'));
						break;
					
					case 0:
						WNote::error('user_login_error', WLang::get('login_error'));
						break;
				}
			} else {
				WNote::error('user_bad_data', WLang::get('bad_data'));
			}
			
			// Login process triggered from an external application
			if ($cookie && strpos($referer, 'user') === false) {
				// Redirect to it
				header('location: '.$referer);
				return;
			}
		}
		$this->view->connexion($redirect);
	}
	
	/**
	 * Logout action handler
	 * 
	 * @todo Smartly find the redirecting URL based on the referer (watch out to app requiring special access)
	 */
	protected function logout() {
		if ($this->session->isConnected()) {
			// Destroy the session of the user
			$this->session->closeSession();
		}
		WNote::success('user_disconnected', WLang::get('user_disconnected'));
		header('location: '.WRoute::getBase());
	}
	
	/**
	 * Register action handler
	 * 
	 * @todo Captcha security
	 */
	protected function register() {
		// Check if inscriptions are open
		$config = $this->model->getConfig();
		if (!$config['register']) {
			WNote::info('user_inscription_closed', WLang::get('user_inscription_closed'), 'display');
			return;
		}
		
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'country'));
			if (!in_array(null, $data, true)) {
				$errors = array();
				
				// Check nickname availabililty
				if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
					$errors[] = WLang::get($e);
				}
				
				// Matching passwords
				if (!empty($data['password'])) {
					if ($data['password'] === $data['password_conf']) {
						$data['password'] = sha1($data['password']);
					} else {
						$errors[] = WLang::get('error_password_not_matching');
					}
				} else {
					$errors[] = WLang::get('error_no_password');
				}
				
				// Email availabililty
				if (($e = $this->model->checkEmail($data['email'])) !== true) {
					$errors[] = WLang::get($e);
				}
				
				// Default group (0: simple user)
				$data['groupe'] = 0;
				
				if (empty($errors)) {
					// Configure user
					if ($config['email_conf']) {
						$data['confirm'] = uniqid(); // Set a confirm code
						$data['valid'] = 0; // account not valid
					} else if ($config['admin_check']) {
						$data['valid'] = 2; // value to require admin check
					}
					
					if ($this->model->createUser($data)) {
						if ($config['email_conf']) {
							// Send a validation email
							$this->model->sendEmail(
								$data['email'],
								WLang::get('user_register_email_subject', WConfig::get('config.site_name')),
								str_replace(
									array('{site_name}', '{nickname}', '{password}', '{base}', '{confirm}'),
									array(WConfig::get('config.site_name'), $data['nickname'], $data['password_conf'], WRoute::getBase(), $data['confirm']),
									WLang::get('user_register_email_confirm')
								)
							);
							
							WNote::success('user_register_confirm', WLang::get('user_register_confirm'), 'display');
						} else if ($config['admin_check']) {
							if ($config['summary']) {
								// Send an email to the user to remind him its login data
								$this->model->sendEmail(
									$data['email'],
									WLang::get('user_register_email_subject', WConfig::get('config.site_name')),
									str_replace(
										array('{site_name}', '{nickname}', '{password}'),
										array(WConfig::get('config.site_name'), $data['nickname'], $data['password_conf']),
										WLang::get('user_register_email_admin')
									)
								);
							}
							
							// Send email to the administrators to warn them
							$admin_emails = WConfig::get('config.email');
							if (!empty($admin_emails)) {
								$userid = $this->model->getLastUserId();
								$this->model->sendEmail(
									$admin_emails,
									WLang::get('user_register_email_subject', WConfig::get('config.site_name')),
									str_replace(
										array('{site_name}', '{nickname}', '{base}', '{userid}'),
										array(WConfig::get('config.site_name'), $data['nickname'], WRoute::getBase(), $userid),
										WLang::get('user_register_admin_warning')
									)
								);
							}
							WNote::success('user_register_admin', WLang::get('user_register_admin'), 'display');
						} else {
							if ($config['summary']) {
								// Send a validation email
								$this->model->sendEmail(
									$data['email'],
									WLang::get('user_register_email_subject', WConfig::get('config.site_name')),
									str_replace(
										array('{site_name}', '{nickname}', '{password}', '{base}'),
										array(WConfig::get('config.site_name'), $data['nickname'], $data['password_conf'], WRoute::getBase()),
										WLang::get('user_register_email')
									)
								);
							}
							WNote::success('user_register_success', WLang::get('user_register_success'), 'display');
						}
						return;
					} else {
						WNote::error('user_register_failure', WLang::get('user_register_failure'));
					}
				} else {
					WNote::error('user_data_errors', implode("<br />\n", $errors));
				}
			} else {
				WNote::error('user_bad_data', WLang::get('bad_data'));
			}
			$this->view->register($data);
		} else {
			$this->view->register();
		}
	}
	
	/**
	 * Confirm action handler
	 * Allows the user to validate its account after registering
	 */
	protected function confirm() {
		// Check if inscriptions are open
		$config = $this->model->getConfig();
		if (!$config['register']) {
			WNote::info('user_register_closed', WLang::get('user_register_closed'), 'display');
			return;
		}
		
		// Retrieve the confirm code
		$confirm_code = WRoute::getArg(1);
		if (empty($confirm_code)) {
			header('location: '.WRoute::getBase());
			return;
		}
		
		$data = $this->model->findUserWithConfirmCode($confirm_code);
		if (empty($data)) {
			WNote::error('user_invalid_confirm_code', WLang::get('user_invalid_confirm_code'), 'display');
			return;
		}
		
		if ($config['admin_check']) {
			if ($this->model->updateUser($data['id'], array('confirm' => '', 'valid' => 2))) {
				// Send email to the administrators to warn them
				$admin_emails = WConfig::get('config.email');
				if (!empty($admin_emails)) {
					$userid = $this->model->getLastUserId();
					$this->model->sendEmail(
						$admin_emails,
						WLang::get('user_register_email_subject', WConfig::get('config.site_name')),
						str_replace(
							array('{site_name}', '{nickname}', '{base}', '{userid}'),
							array(WConfig::get('config.site_name'), $data['nickname'], WRoute::getBase(), $userid),
							WLang::get('user_register_admin_warning')
						)
					);
				}
				WNote::success('user_validated_admin', WLang::get('user_validated_admin'), 'display');
			} else {
				WNote::error('user_register_failure', WLang::get('user_register_failure'));
			}
		} else {
			if ($this->model->updateUser($data['id'], array('confirm' => '', 'valid' => 1))) {
				WNote::success('user_validated', WLang::get('user_validated'));
			} else {
				WNote::error('user_register_failure', WLang::get('user_register_failure'));
			}
			$this->view->connexion();
		}
	}
	
	/**
	 * Password-lost action handler
	 * Triggered when a user wants to recover its password
	 */
	protected function password_lost() {
		$data = WRequest::getAssoc(array('email', 'confirm'));
		if (empty($data['email']) || empty($data['confirm'])) { // Step 1 - Ask for email
			$email = WRequest::get('email', null, 'POST');
			if (!empty($email)) {
				$user_data = $this->model->findUserWithEmail($email);
				if (!empty($user_data)) {
					// Create a uniq confirm code
					$confirm = uniqid();
					if ($this->model->updateUser($user_data['id'], array('confirm' => $confirm))) {
						// Send it by email
						$this->model->sendEmail(
							$data['email'],
							WLang::get('user_password_lost_subject', WConfig::get('config.site_name')),
							str_replace(
								array('{site_name}', '{email}', '{confirm}'),
								array(WConfig::get('config.site_name'), $user_data['email'], $confirm),
								WLang::get('user_password_lost_email')
							)
						);
						WNote::success('user_password_lost_email_sent', WLang::get('user_password_lost_email_sent'), 'display');
						return;
					} else {
						WNote::error('user_password_lost_failure', WLang::get('user_password_lost_failure'));
					}
				} else {
					WNote::error('user_password_lost_not_found', WLang::get('user_password_lost_not_found'));
				}
			}
			$this->view->password_lost();
		} else { // Step 2 - Reset password
			$user_data = $this->model->findUserWithEmailAndConfirmCode($data['email'], $data['confirm']);
			if (!empty($user_data)) {
				$pass = WRequest::getAssoc(array('new_password', 'new_password_conf'));
				// Check passwords
				if (!empty($pass['new_password'])) {
					if ($pass['new_password'] === $pass['new_password_conf']) {
						// Reset password in the database
						$password = sha1($pass['new_password']);
						if ($this->model->updateUser($user_data['id'], array('password' => $password, 'confirm' => ''))) {
							WNote::success('user_password_lost_success', WLang::get('user_password_lost_success'), 'display');
						} else {
							WNote::error('user_password_lost_failure', WLang::get('user_password_lost_failure'));
						}
					} else {
						WNote::error('error_password_not_matching', WLang::get('error_password_not_matching'));
					}
				}
				$this->view->reset_password($data['email'], $data['confirm']);
			} else {
				header('location: '.WRoute::getBase());
			}
		}
	}
}

?>
