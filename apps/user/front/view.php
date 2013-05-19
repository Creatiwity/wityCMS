<?php
/**
 * User Application - View - /apps/user/front/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserView is the front View of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-26-02-2013
 */
class UserView extends WView {
	private $model;
	
	public function __construct(UserModel $model) {
		parent::__construct();
		$this->model = $model;
		$this->assign('css', '/apps/user/front/css/user.css');
	}
	
	/**
	 * Prepares the connexion form
	 * 
	 * @param string $redirect The redirect value to set in the input form
	 */
	public function connexion($redirect = '') {
		$this->assign('redirect', $redirect);
		$this->render('connexion_form');
	}
	
	public function register(array $data = array()) {
		$this->assign('base', WRoute::getBase());
		$inputs = array('nickname', 'email', 'firstname', 'lastname', 'country');
		foreach ($inputs as $name) {
			$this->assign($name, isset($data[$name]) ? $data[$name] : '');
		}
		$this->render('register');
	}
	
	public function password_lost() {
		$this->render('password_lost');
	}
	
	public function reset_password($email, $confirm) {
		$this->assign('email', $email);
		$this->assign('confirm', $confirm);
		$this->render('reset_password');
	}
}

?>