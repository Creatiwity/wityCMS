<?php
/**
 * User Application - Controller - /apps/user/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserController is the front Controller of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-29-01-2013
 */
class UserController extends WController {
	/*
	 * Default session life when the user asks to remember his account = 1 week
	 */
	const REMEMBER_TIME = 604800;
	
	/*
	 * Maximum login attempts
	 */
	const MAX_LOGIN_ATTEMPT = 3;
	
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
	 * Custom launch method
	 */
	public function launch() {
		$action = $this->getAskedAction();
		switch ($action) {
			case 'connexion':
				$this->forward('login');
				break;
			
			case 'deconnexion':
				$this->forward('logout');
				break;
			
			default:
				$this->forward($action);
				break;
		}
	}
	
	/**
	 * Login action handler
	 * Triggered whenever a user asks to be connected
	 * 
	 * @param string $redirect URL to redirect the request
	 */
	protected function login($redirect = '') {
		if ($this->session->isConnected()) {
			WNote::error('user_already_connected', 'No need to access to the login form since you are already connected.', 'display');
			return;
		}
		
		// Find redirect URL
		$referer = WRoute::getReferer();
		$redirect_request = WRequest::get('redirect');
		if (empty($redirect)) {
			if (!empty($redirect_request)) {
				$redirect = $redirect_request;
			} else {
				// Login form may be loaded from an external application
				$redirect = (WRoute::getApp() != 'user') ? WRoute::getURL() : $referer;
			}
		}
		
		// Vars given to trigger login process?
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'remember', 'time'));
			if (!empty($data['nickname']) && !empty($data['password'])) {
				// User asks to be auto loged in => change the cookie lifetime to self::REMEMBER_TIME
				$remember_time = !empty($data['remember']) ? self::REMEMBER_TIME : abs(intval($data['time'])) * 60;
				
				// Start login process
				switch ($this->session->createSession($data['nickname'], $data['password'], $remember_time)) {
					case WSession::LOGIN_SUCCESS:
						// Update activity
						$this->model->updateLastActivity($_SESSION['userid']);
						
						// Redirect
						WNote::success('login_success', sprintf('Welcome %s!', $_SESSION['nickname']));
						header('location: '.$redirect);
						return;
					
					case WSession::LOGIN_MAX_ATTEMPT_REACHED:
						WNote::error('login_max_attempt', "You reached the maximum of login attempt authorized.\nPlease, wait a moment before trying again.");
						break;
					
					case 0:
						WNote::error('login_error', "The <em>nickname / password</em> given do not match.");
						break;
				}
			} else {
				WNote::error('bad_data', "Please, fill in all the required fields.");
			}
			
			// Login process triggered from an external application
			if (strpos($referer, 'user') === false) {
				// Redirect to it
				header('location: '.$referer);
			} else {
				$this->view->connexion($redirect);
			}
		} else {
			$this->view->connexion($redirect);
		}
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
		WNote::success('user_disconnected', "You were loged out successfully.");
		header('location: '.WRoute::getBase());
	}
	
	/**
	 * Register action handler
	 * 
	 * @todo Send an email or not
	 * @todo Account auto validation (no need to ask confirmation by mail)
	 * @todo Account validated by admin
	 * @todo Captcha security
	 */
	protected function register() {
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'adress', 'zipcode', 'city'));
			if (!in_array(null, $data, true)) {
				$errors = array();
				
				// Check nickname availabililty
				if ($e = $this->model->checkNickname($data['nickname']) !== true) {
					$errors[] = $e;
				}
				
				// Matching passwords
				if (!empty($data['password'])) {
					if ($data['password'] === $data['password_conf']) {
						$data['password'] = sha1($data['password']);
					} else {
						$errors[] = "The password given is not the same as the password confirmation.";
					}
				} else {
					$errors[] = "No password given.";
				}
				
				// Email availabililty
				if ($e = $this->model->checkEmail($data['email']) !== true) {
					$errors[] = $e;
				}
				
				// Confirmation hash + group
				$data['confirm'] = uniqid();
				$data['groupe'] = 0;
				
				if (empty($errors)) {
					// Create the user
					if ($this->model->createUser($data)) {
						// Send a validation email
						$mail = WHelper::load('phpmailer');
						$mail->CharSet = 'utf-8';
						$mail->From = WConfig::get('config.email');
						$mail->FromName = WConfig::get('config.site_name');
						$mail->Subject = sprintf('Your account registration on %s', WConfig::get('config.site_name'));
						$mail->Body = 
"Bonjour,<br /><br />
Vous venez de vous inscrire sur le site ".WConfig::get('config.site_name').".<br /><br />

Veuillez trouver ci-dessous vos données de connexion :<br />
Identifiant : ".$data['nickname']."<br />
Password : ".$data['password_conf']."<br /><br />

Pour finaliser votre demande, veuillez cliquer sur le lien ci-dessous :<br /><br />
<a href=\"".WRoute::getBase()."/user/confirm/".$data['confirm']."\">Valider la demande</a><br /><br />

Si ce lien ne fonctionne pas, veuillez copier l'adresse suivante dans votre navigateur :<br />
".WRoute::getBase()."/user/confirm/".$data['confirm']."<br /><br />

<strong>".WConfig::get('config.site_name')."</strong>";
						$mail->IsHTML(true);
						$mail->AddAddress($data['email']);
						$mail->Send();
						unset($mail);
						
						WNote::success('user_registered', "Your account on %s was created successfully.\n\nYou are going to receive in your mailbox a new message to validate your account.", 'display');
					} else {
						WNote::error('user_registration_failure', "An unknown error occured when trying to create your account in the database.");
						header('location: '.WRoute::getBase());
					}
				} else {
					WNote::error('data_errors', implode("<br />\n", $errors));
					header('location: '.WRoute::getReferer());
				}
			} else {
				header('location: '.WRoute::getBase());
			}
		} else {
			// @todo Display a registration form
		}
	}
	
	/**
	 * Confirm action handler
	 * Triggered when a user wants to validate his account
	 */
	protected function confirm() {
		// Retrieve the confirm code
		list(, $confirm_code) = WRoute::getArgs();
		if (!empty($confirm_code)) {
			$this->model->validateAccount($confirm_code);
			WNote::success('user_validated', "Your account got validated successfully. You can now login with it.", 'display');
		} else {
			header('location: '.WRoute::getBase());
		}
	}
}

?>
