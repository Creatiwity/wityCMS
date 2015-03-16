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
 * @version 0.5.0-dev-02-10-2013
 */
class ContactController extends WController {

	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'contact'.DS;
	}

	protected function form(array $params) {
		$user_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
		$data = WRequest::getAssoc(array('from_name', 'from_email', 'email_subject', 'email_message'));
		
		if (!in_array(null, $data, true)) {
			$data['from_company'] = WRequest::get('from_company');
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

			// Attachment
			if (!empty($_FILES['document']['name'])) {
				$this->makeUploadDir();

				$upload = WHelper::load('upload', array($_FILES['document']));
				$upload->allowed = array(
					'application/vnd.ms-excel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.ms-powerpoint',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
					'application/vnd.ms-word',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/pdf',
					'image/*');
				
				$upload->Process($this->upload_dir);

				if (!$upload->processed) {
					$errors[] = WLang::get('upload_error');
					// TODO : Manage upload helper errors
				} else {
					$data['attachment'] = $upload->file_dst_pathname;
				}
			} else {
				$data['attachment'] = '';
			}

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
								'subject' => WLang::get('mail_for_admin_subject', WConfig::get('config.site_title'), $data['email_subject']),
								'body' => WLang::get('mail_for_admin_body', array(
									'site'    => WConfig::get('config.site_title'),
									'base'    => WRoute::getBase(),
									'name'    => $data['from_name'].' &lt;'.$data['from_email'].'&gt;',
									'company' => $data['from_company'],
									'subject' => $data['email_subject'],
									'message' => $data['email_message']
								)),
								'attachments' => !empty($data['attachment']) ? array($data['attachment']) : array()
							),
							array(
								'from' => array($config['site_from_email'], $config['site_from_name']),
								'to' => array($data['from_email'], $data['from_name']),
								'subject' => WLang::get('copy_subject', WConfig::get('config.site_title')),
								'body' => WLang::get('auto_reply', array(
									'site'    => WConfig::get('config.site_title'),
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

	private function makeUploadDir($suffix = '') {
		if (!is_dir($this->upload_dir.$suffix) && mkdir($this->upload_dir.$suffix, 0775, true)) {
			$htaccess = fopen($this->upload_dir.$suffix.'.htaccess', 'a+');
			fwrite($htaccess, 'deny from all');
			fclose($htaccess);
		}
	}

}

?>
