<?php
/**
 * Contact Application - Front View
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactView is the Front View of the Contact Application
 *
 * @package Apps\Contact\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-02-10-2013
 */
class ContactView extends WView {

	public function form($data) {
		$this->assign('css', '/apps/contact/front/css/contact.css');
		$this->assign('require', 'apps!contact');
		$this->assign('from_email', $data['from_email']);
		$this->assign('from_name', $data['from_name']);
	}

}

?>
