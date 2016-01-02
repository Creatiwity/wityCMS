<?php
/**
 * Newsletter Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterController is the Front Controller of the Newsletter Application
 *
 * @package Apps\Newsletter\Front
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 0.5.0-dev-02-01-2015
 */
class NewsletterController extends WController {
	public function add(array $params) {
		$data = WRequest::getAssoc(array('email'));

		if (!empty($data['email'])) {
			if (!WTools::isEmail($data['email'])) {
				return WNote::error('bad_email');
			}

			if ($this->model->isEmailInDb($data['email'])) {
				return WNote::error('already_in_db');
			}

			if ($this->model->addEmail($data['email'])) {
				return WNote::success('email_added');
			} else {
				return WNote::error('unknown_error');
			}
		}
	}
}

?>
