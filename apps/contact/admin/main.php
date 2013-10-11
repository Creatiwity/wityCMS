<?php
/**
 * Contact Application - Admin Controller - /apps/contact/admin/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactAdminController is the Admin Controller of the Contact Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-07-10-2013
 */
class ContactAdminController extends WController {
	public function __construct() {
		include 'model.php';
		$this->model = new ContactAdminModel();
		
		include 'view.php';
		$this->setView(new ContactAdminView());
	}
	
	protected function mail_history(array $params) {

	}

	protected function new_mail(array $params) {
		
	}
}

?>
