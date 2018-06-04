<?php
/**
 * User Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserView is the Front View of the User Application.
 *
 * @package Apps\User\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class UserView extends WView {
	public function __construct() {
		parent::__construct();

		// CSS for all views
		$this->assign('css', '/apps/user/front/css/user.css');
	}

	/**
	 * Prepares the connexion form.
	 *
	 * @param array $model Model containing the redirect link
	 */
	public function login($model) {
		$this->assign('redirect', $model['redirect']);
		$this->assign('config', $model['config']);
	}

	/**
	 * Prepares the register form.
	 *
	 * @param array $model
	 */
	public function register($model) {
		$inputs = array('nickname', 'email', 'firstname', 'lastname', 'country');
		foreach ($inputs as $name) {
			$this->assign($name, isset($model[$name]) ? $model[$name] : '');
		}
	}

	/**
	 * Prepares the password-lost form.
	 *
	 * @param array $model
	 */
	public function password_lost($model) {
		// Reset password
		if ($model['step'] == 2) {
			$this->assign('email', $model['email']);
			$this->assign('confirm', $model['confirm']);
			$this->setTemplate('reset_password.html');
		}
	}
}

?>
