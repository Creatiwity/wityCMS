<?php
/**
 * Contact Application - Front View - /apps/contact/front/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactView is the Front View of the Contact Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-02-10-2013
 */
class ContactView extends WView {

	public function form($data) {
		$this->assign('js', '/apps/contact/front/js/validator.js');
		$this->assign('from_email', $data['from_email']);
		$this->assign('from_name', $data['from_name']);
	}

}

?>