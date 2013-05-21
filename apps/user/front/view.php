<?php
/**
 * User Application - View - /apps/user/front/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserView is the front View of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-26-02-2013
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
	public function connexion($redirect = '') {
		$this->assign('redirect', $redirect);
	}
	
	public function register(array $data = array()) {
		$this->assign('base', WRoute::getBase());
		$inputs = array('nickname', 'email', 'firstname', 'lastname', 'country');
		foreach ($inputs as $name) {
			$this->assign($name, isset($data[$name]) ? $data[$name] : '');
		}
	}
	
	public function reset_password($email, $confirm) {
		$this->assign('email', $email);
		$this->assign('confirm', $confirm);
	}
}

?>