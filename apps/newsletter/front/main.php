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
 * @version 0.6.2-04-06-2018
 */
class NewsletterController extends WController {
	public function add(array $params) {
		$data = WRequest::getAssoc(array('email'));

		if (!empty($data['email'])) {
			if (!WTools::isEmail($data['email'])) {
				return WNote::error('Email invalid.');
			}

			if ($this->model->isEmailInDb($data['email'])) {
				return WNote::error('This e-mail address is already subscribed.');
			}

			if ($this->model->addEmail($data['email'])) {
				return WNote::success('E-mail address successfully added.');
			} else {
				return WNote::error('An unknown error happened.');
			}
		}
	}
}

?>
