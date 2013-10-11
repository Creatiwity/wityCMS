<?php
/**
 * Contact Application - Admin Model - /apps/contact/admin/model.php
 */

defined('IN_WITY') or die('Access denied');

// Include Front Model for inheritance
include_once APPS_DIR.'contact'.DS.'front'.DS.'model.php';

/**
 * ContactAdminModel is the Admin Model of the News Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-07-10-2013
 */
class ContactAdminModel extends ContactModel {
	public function __construct() {
		parent::__construct();
	}

	public function getEmails(array $filter) {

	}
}

?>
