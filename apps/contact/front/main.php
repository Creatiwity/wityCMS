<?php
/**
 * Contact Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

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
				
				if ($this->sendMail($data)) {
					$this->setHeader('Location', Wroute::getDir());
					return WNote::success('email_sent', WLang::get('email_sent'));
				} else {
					return WNote::error('email_not_sent', WLang::get('email_not_sent'));
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
	
	/**
	 * Sends an email to the sender and the recipient.
	 * 
	 * @param array $params
	 * @return bool Success state
	 */
	private function sendMail(array $params) {
		$config = $this->model->getConfig();
		
		if (empty($config['site_from_name']) || empty($config['site_from_email'])) {
			WNote::error('missing_configuration', WLang::get('missing_configuration', serialize($params)), 'email');
			return false;
		}
		
		$params['to'] = array();
		$params['to'][] = array($config['site_from_email'], $config['site_from_name']);
		$params['reply_to'] = array();
		$params['reply_to'][] = array($params['from_email'], $params['from_name']);
		
		$universalAdd = function ($param, $key, $fn) {
			if (isset($param[$key])) {
				$param = $param[$key];

				if (!empty($param)) {
					if (!is_array($param)) {
						call_user_func($fn, $param);
					} else {
						foreach ($param as $val) {
							if (is_array($val)) {
								call_user_func($fn, $val[0], $val[1]);
							} else {
								call_user_func($fn, $val);
							}
						}
					}
				}
			}
		};
		
		// Send mail
		$phpmailer = WHelper::load("phpmailer");
		$phpmailer->CharSet = 'utf-8';
		$phpmailer->From = $params['from_email'];
		$phpmailer->FromName = $params['from_name'];
		
		$universalAdd($params, 'to', array($phpmailer, 'addAddress'));
		// $universalAdd($params, 'cc', array($phpmailer, 'addCC'));
		// $universalAdd($params, 'bcc', array($phpmailer, 'addBCC'));
		$universalAdd($params, 'reply_to', array($phpmailer, 'addReplyTo'));
		
		$phpmailer->isHTML(true);
		$phpmailer->Subject = WLang::get('mail_for_admin_subject', array(WConfig::get('config.site_name'), $params['email_subject']));
		$phpmailer->Body = WLang::get('mail_for_admin_body', array(
			'site'    => WConfig::get('config.site_name'),
			'base'    => WRoute::getBase(),
			'name'    => $params['from_name'].' &lt;'.$params['from_email'].'&gt;',
			'company' => $params['from_company'],
			'subject' => $params['email_subject'],
			'message' => $params['email_message']
		));
		
		if (!$phpmailer->send()) {
			return false;
		}
		
		unset($phpmailer);
		
		// Send mail to expeditor
		$phpmailer = WHelper::load("phpmailer");
		$phpmailer->CharSet = 'utf-8';
		$phpmailer->From = $config['site_from_email'];
		$phpmailer->FromName = $config['site_from_name'];
		
		$universalAdd(array(array(array($params['from_email'], $params['from_name']))), 0, array($phpmailer, 'addAddress'));
		
		$phpmailer->isHTML(true);
		$phpmailer->Subject = WLang::get('copy_subject', WConfig::get('config.site_name'));
		$phpmailer->Body = WLang::get('auto_reply', array(
			'site'    => WConfig::get('config.site_name'),
			'name'    => $params['from_name'].' &lt;'.$params['from_email'].'&gt;',
			'company' => $params['from_company'],
			'subject' => $params['email_subject'],
			'message' => $params['email_message']
		));
		
		if (!$phpmailer->send()) {
			return false;
		}
		
		if (!$this->model->addMail($params)) {
			WNote::error('unable_to_save_email', WLang::get('unable_to_save_email', serialize($params)), 'email');
		}
		
		return true;
	}
	
}

?>
