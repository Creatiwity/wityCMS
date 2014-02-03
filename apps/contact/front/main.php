<?php
/**
 * Contact Application - Front Controller
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactController is the Front Controller of the Contact Application
 *
 * @package Apps\Contact\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-02-10-2013
 */
class ContactController extends WController {

	protected function form(array $params) {
		$user_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;

		if (WRequest::hasData()) {
			$data = WRequest::getAssoc(array('from_name', 'from_company', 'from_email', 'email_subject', 'email_message'));
			$errors = array();

			/**
			 * BEGIN VARIABLES CHECKING
			 */
			if (empty($data['from_name'])) {
				$errors[] = WLang::get("no_from_name");
			}

			if (empty($data['from_email'])) {
				$errors[] = WLang::get("no_from_email");
			} else if (!preg_match('#^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$#i', $data['from_email'])) {
				$errors[] = WLang::get("invalid_from_email");
			}

			if (empty($data['email_subject'])) {
				$errors[] = WLang::get("no_email_subject");
			}

			if (empty($data['email_message'])) {
				$errors[] = WLang::get("no_email_message");
			}

			$data['email_message'] = nl2br($data['email_message']);
			/**
			 * END VARIABLES CHECKING
			 */

			if (empty($errors)) {
				if (!is_null($user_id)) {
					$data['userid'] = $user_id;
				}

				$config = $this->model->getConfig();

				if (empty($config['site_from_name']) || empty($config['site_from_email'])) {
					WNote::error('missing_configuration', WLang::get('missing_configuration', serialize($data)), 'email');
					return WNote::error('email_not_sent', WLang::get('email_not_sent'));
				} else {
					$data['to'] = array($config['site_from_email'], $config['site_from_name']);

					$mail = array(
						'origin' => array(
							'app' => 'contact',
							'action' => 'form',
							'parameters' => array()
						),
						'defaults' => array(),
						'specifics' => array(
							array(
								'from' => array($data['from_email'], $data['from_name']),
								'to' => $data['to'],
								'subject' => WLang::get('mail_for_admin_subject', WConfig::get('config.site_name'), $data['email_subject']),
								'body' => WLang::get('mail_for_admin_body', array(
									'site'    => WConfig::get('config.site_name'),
									'base'    => WRoute::getBase(),
									'name'    => $data['from_name'].' &lt;'.$data['from_email'].'&gt;',
									'company' => $data['from_company'],
									'subject' => $data['email_subject'],
									'message' => $data['email_message']
								))
							),
							array(
								'from' => array($config['site_from_email'], $config['site_from_name']),
								'to' => array($data['from_email'], $data['from_name']),
								'subject' => WLang::get('copy_subject', WConfig::get('config.site_name')),
								'body' => WLang::get('auto_reply', array(
									'site'    => WConfig::get('config.site_name'),
									'name'    => $data['from_name'].' &lt;'.$data['from_email'].'&gt;',
									'company' => $data['from_company'],
									'subject' => $data['email_subject'],
									'message' => $data['email_message']
								))
							)
						)
					);

					$mail_app = WRetriever::getModel('mail', $mail);

					if (!$this->model->addMail($data)) {
						WNote::error('unable_to_save_email', WLang::get('unable_to_save_email', serialize($data)), 'email');
					}

					if (empty($mail_app['result']) || empty($mail_app['result']['success']) || $mail_app['result']['success'] != true) {
						return WNote::error('email_not_sent', WLang::get('email_not_sent'));
					}

					$this->setHeader('Location', Wroute::getDir());
					return WNote::success('email_sent', WLang::get('email_sent'));
				}

			} else {
				return WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}

		// Load form
		$model = array(
			'from_name'  => '',
			'from_email' => ''
		);

		if (!is_null($user_id)) { // Add name and email
			if (!empty($_SESSION['firstname'])) {
				$model['from_name'] .= $_SESSION['firstname'].' ';
			}
			$model['from_name'] .= $_SESSION['lastname'];

			$model['from_email'] = $_SESSION['email'];
		}

		return $model;
	}

}

?>
