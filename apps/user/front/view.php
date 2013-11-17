<?php
/**
 * User Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserView is the front View of the User Application.
 * 
 * @package Apps\User\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-02-2013
 */
class UserView extends WView {
	public function __construct() {
		parent::__construct();
		$this->assign('css', '/apps/user/front/css/user.css');
	}
	
	/**
	 * Prepares the connexion form
	 * 
	 * @param string $redirect The redirect value to set in the input form
	 */
	public function login($model) {
		$this->assign('redirect', $model['redirect']);
		$this->setTemplate('connexion_form');
	}
	
	public function register($data = array()) {
		$this->assign('base', WRoute::getBase());
		$inputs = array('nickname', 'email', 'firstname', 'lastname', 'country');
		foreach ($inputs as $name) {
			$this->assign($name, isset($data[$name]) ? $data[$name] : '');
		}
	}
	
	public function password_lost($model) {
		// Reset password
		if ($model['step'] == 2) {
			$this->assign('email', $model['email']);
			$this->assign('confirm', $model['confirm']);
			$this->setTemplate('reset_password');
		}
	}
}

?>
