<?php
/**
 * User Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserController is the Front Controller of the User Application.
 *
 * @package Apps\User\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class UserController extends WController {
	/*
	 * @var Instance of WSession
	 */
	private $session;

	/**
	 * UserController's constructor to initialize $session.
	 */
	public function __construct() {
		$this->session = WSystem::getSession();
	}

	/**
	 * The Login action allows a user to connect to his account.
	 *
	 * @param array $params Redirect is expected in this array
	 * @return array Model containing the redirect link
	 */
	protected function login($params) {
		$redirect = $this->model->getRedirectURLWithParams($params);

		if ($this->session->isConnected()) {
			$this->setHeader('Location', $redirect);
			return WNote::error('user_already_connected', 'No need to access to the login form since you are already connected.');
		}

		if (WRequest::hasDataForURL('user/login')) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'remember', 'time'));
			$cookie = true; // cookies accepted by browser?

			if (!empty($data['nickname']) && !empty($data['password'])) {
				// User asks to be auto loged in => change the cookie lifetime to WSession::REMEMBER_TIME
				$remember_time = !empty($data['remember']) ? WSession::REMEMBER_TIME : abs(intval($data['time'])) * 60;

				// Start login process
				switch ($this->session->createSession($data['nickname'], $data['password'], $remember_time)) {
					case WSession::LOGIN_SUCCESS:
						// Update activity
						$this->model->updateLastActivity($_SESSION['userid']);

						if (empty($_COOKIE['wsid'])) {
							$cookie = false;
							WNote::info('user_cookie_not_accepted', WLang::get('To log in, your browser must accept cookies.'));
						} else {
							WNote::success('user_login_success', WLang::get('Welcome %s!', $_SESSION['nickname']));
						}
						break;

					case WSession::LOGIN_ERROR:
						WNote::error('user_login_error', WLang::get('The couple nickname / password does not match with any valid account.'));
						break;

					case WSession::LOGIN_MAX_ATTEMPT_REACHED:
						WNote::error('user_login_max_attempt', WLang::get('You reached the maximum number of login attempts. Please, wait a few moment before trying to login again.'));
						break;
				}
			} else {
				WNote::error('user_bad_data', WLang::get('Please, fill in all the required fields.'));
			}

			$this->setHeader('Location', $redirect);
		}

		return array(
			'redirect' => $redirect,
			'config'   => $this->model->getConfig()
		);
	}

	/**
	 * Logout action handler.
	 *
	 * @return array Success note
	 */
	protected function logout() {
		if ($this->session->isConnected()) {
			// Destroy the session of the user
			$this->session->closeSession();
		}

		$this->setHeader('Location', WRoute::getDir());
		return WNote::success('user_disconnected', WLang::get('You were successfully logout.'));
	}

	/**
	 * The Register action allows a user to register a new account.
	 *
	 * @return array Data given
	 */
	protected function register() {
		// Check if inscriptions are open
		$config = $this->model->getConfig();
		if (!$config['register']) {
			return WNote::info('user_inscription_closed', WLang::get('Sorry, but the registration is closed on this website.'));
		}

		if (WRequest::hasDataForURL('user/register')) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'country'));
			$errors = array();
			$data['nickname'] = trim($data['nickname']);
			$data['password'] = trim($data['password']);
			$data['password_conf'] = trim($data['password_conf']);

			// Check nickname availability
			if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
				$errors[] = $e;
			}

			// Matching passwords
			if (!empty($data['password'])) {
				if ($data['password'] === $data['password_conf']) {
					$data['password'] = sha1($data['password']);
				} else {
					$errors[] = WLang::get('The passwords do not match.');
				}
			} else {
				$errors[] = WLang::get('Please, provide a password.');
			}

			// Email availability
			if (($e = $this->model->checkEmail($data['email'])) !== true) {
				$errors[] = $e;
			} else {
				$data['email'] = strtolower($data['email']);
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

				$user_id = $this->model->createUser($data);
				if ($user_id !== false) {
					if ($config['email_conf']) {
						// Send a validation email
						$this->model->sendEmail(
							$data['email'],
							WLang::get('%s - User account creation', WConfig::get('config.site_title')),
							str_replace(
								array('{site_title}', '{nickname}', '{password}', '{base}', '{confirm}'),
								array(WConfig::get('config.site_title'), $data['nickname'], $data['password_conf'], WRoute::getBase(), $data['confirm']),
								WLang::get('user_register_email_confirm')
							)
						);

						return WNote::success('user_register_confirm', WLang::get('To complete the creation of your account, please, click on the link in the email you are going to receive.'));
					} else if ($config['admin_check']) {
						if ($config['summary']) {
							// Send an email to the user to remind him its login data
							$this->model->sendEmail(
								$data['email'],
								WLang::get('%s - User account creation', WConfig::get('config.site_title')),
								str_replace(
									array('{site_title}', '{nickname}', '{password}'),
									array(WConfig::get('config.site_title'), $data['nickname'], $data['password_conf']),
									WLang::get('user_register_email_admin')
								)
							);
						}

						// Send email to the administrators to warn them
						$admin_emails = WConfig::get('config.email');
						if (!empty($admin_emails)) {
							$this->model->sendEmail(
								$admin_emails,
								WLang::get('%s - User account creation', WConfig::get('config.site_title')),
								str_replace(
									array('{site_title}', '{nickname}', '{base}', '{userid}'),
									array(WConfig::get('config.site_title'), $data['nickname'], WRoute::getBase(), $user_id),
									WLang::get('user_register_admin_warning')
								)
							);
						}

						return WNote::success('user_register_admin', WLang::get('user_register_admin'));
					} else {
						if ($config['summary']) {
							// Send a validation email
							$this->model->sendEmail(
								$data['email'],
								WLang::get('%s - User account creation', WConfig::get('config.site_title')),
								str_replace(
									array('{site_title}', '{nickname}', '{password}', '{base}'),
									array(WConfig::get('config.site_title'), $data['nickname'], $data['password_conf'], WRoute::getBase()),
									WLang::get('user_register_email')
								)
							);
						}

						return WNote::success('user_register_success', WLang::get('user_register_success'));
					}
				} else {
					WNote::error('user_register_failure', WLang::get('An unknown error occured.'));
				}
			} else {
				WNote::error('user_data_errors', implode("<br />\n", $errors));
			}
		}
	}

	/**
	 * The Confirm action allows the user to validate its account after registering.
	 *
	 * @param array $params
	 * @return void
	 */
	protected function confirm($params) {
		// Check if inscriptions are open
		$config = $this->model->getConfig();
		if (!$config['register']) {
			return WNote::info('user_register_closed', WLang::get('Sorry, but the registration is closed on this website.'));
		}

		// Retrieve the confirm code
		$confirm_code = array_shift($params);
		if (empty($confirm_code)) {
			$this->setHeader('Location', WRoute::getDir());
			return;
		}

		$data = $this->model->findUserWithConfirmCode($confirm_code);
		if (empty($data)) { // No confirm code found
			return WNote::error('user_invalid_confirm_code', WLang::get('This confirmation code is invalid.'));
		}

		if ($config['admin_check']) {
			if ($this->model->updateUser($data['id'], array('confirm' => '', 'valid' => 2))) {
				// Send email to the administrators to warn them
				$admin_emails = WConfig::get('config.email');
				if (!empty($admin_emails)) {
					$this->model->sendEmail(
						$admin_emails,
						WLang::get('%s - User account creation', WConfig::get('config.site_title')),
						str_replace(
							array('{site_title}', '{nickname}', '{base}', '{userid}'),
							array(WConfig::get('config.site_title'), $data['nickname'], WRoute::getBase(), $data['id']),
							WLang::get('user_register_admin_warning')
						)
					);
				}

				WNote::success('user_validated_admin', WLang::get('user_validated_admin'));
			} else {
				WNote::error('user_register_failure', WLang::get('An unknown error occcured.'));
			}
		} else {
			if ($this->model->updateUser($data['id'], array('confirm' => '', 'valid' => 1))) {
				WNote::success('user_validated', WLang::get('Your account was successfully created.'));
			} else {
				WNote::error('user_register_failure', WLang::get('An unknown error occcured.'));
			}
		}

		$this->setHeader('Location', WRoute::getDir().'user/login');
	}

	/**
	 * The Password-lost action is triggered when a user wants to recover its password.
	 *
	 * @return array Model
	 */
	protected function password_lost() {
		$data = WRequest::getAssoc(array('email', 'confirm'));

		// Step 1 - Ask for email
		if (empty($data['email']) || empty($data['confirm'])) {
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
							WLang::get('%s - Reset your password', WConfig::get('config.site_title')),
							str_replace(
								array('{base}', '{site_title}', '{email}', '{confirm}'),
								array(WRoute::getBase(), WConfig::get('config.site_title'), $user_data['email'], $confirm),
								WLang::get('user_password_lost_email')
							)
						);

						return WNote::success('user_password_lost_email_sent', WLang::get('An email was sent to your address. It contains a link to proceed to the next step of the password reset.'));
					} else {
						WNote::error('user_password_lost_failure', WLang::get('An unknown error occured.'));
					}
				} else {
					WNote::error('user_password_lost_not_found', WLang::get('This email was not found in the database.'));
				}
			}

			return array('step' => 1);
		}
		// Step 2 - Reset password
		else {
			$user_data = $this->model->findUserWithEmailAndConfirmCode($data['email'], $data['confirm']);

			if (!empty($user_data)) {
				$pass = WRequest::getAssoc(array('new_password', 'new_password_conf'));

				// Check passwords
				if (!empty($pass['new_password'])) {
					if ($pass['new_password'] === $pass['new_password_conf']) {
						// Reset password in the database
						$password = sha1($pass['new_password']);
						if ($this->model->updateUser($user_data['id'], array('password' => $password, 'confirm' => ''))) {
							return WNote::success('user_password_lost_success', WLang::get('user_password_lost_success'));
						} else {
							WNote::error('user_password_lost_failure', WLang::get('An unknown error occured.'));
						}
					} else {
						WNote::error('error_password_not_matching', WLang::get('The passwords do not match.'));
					}
				}

				return array('step' => 2, 'email' => $data['email'], 'confirm' => $data['confirm']);
			} else {
				$this->setHeader('Location', WRoute::getDir());
			}
		}
	}
}

?>
