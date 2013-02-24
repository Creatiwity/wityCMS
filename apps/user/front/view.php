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
 * @version 0.3-12-05-2011
 */
class UserView extends WView {
	private $model;
	
	public function __construct(UserModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	/**
	 * Prepares the connexion form
	 * 
	 * @param string $redirect The redirect value to set in the input form
	 */
	public function connexion($redirect = '') {
		if (empty($_COOKIE['wsid'])) {
			WNote::info('cookie_not_accepted', WLang::get('cookie_not_accepted'));
		}
		$this->assign('redirect', $redirect);
		$this->render('connexion_form');
	}
}

?>