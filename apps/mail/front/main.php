<?php
/**
 * Mail Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * MailController is the Front Controller of the Mail Application
 *
 * @package Apps\Mail\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-25-11-2013
 */
class MailController extends WController {

	/**
	 * @var PHPMailer phpMailer instance
	 */
	private $phpmailer;

	/**
	 * @var WTemplate WTemplate instance
	 */
	private $tpl;

	/**
	 * @var Hash variable that represents current mailing list
	 */
	private $hash_mailing_list;

	/**
	 * @var Hash variable that represents current mail
	 */
	private $hash_mail;

	/**
	 * @var Link expiration method
	 */
	private $expiration;

	/**
	 * @var Response mode, 'all' will answer to all, 'from' will answer to the from email field, 'none' is no-reply@...
	 */
	private $response_policy;

	/**
	 * @var Configuration parameters
	 */
	private $configuration;

	/**
	 * Sends email
	 *
	 * 	$params = array(
	 * 		'response_policy' => 'all'|'from' (default)|'none',
	 * 		'action_expiration' => 'one-time-action'|'one-time-mail'|DateInterval initializer ('P30D' default)|array(['one-time-action', 'one-time-mail', DateInterval initializer]),
	 * 		'response_callback' => '/app_name[/action][/param1[/...]]?hash_mail=hash_mail&hash_action=hash_action[&...]'
	 * 		'defaults' => array(
	 * 			'from' => email|array(email[, name]),
	 * 			'to' => array(email[, name])|array(array(email[, name])),
	 * 			'cc' => array(email[, name])|array(array(email[, name])),
	 * 			'bcc' => array(email[, name])|array(array(email[, name])),
	 * 			'attachments' => array(url[, name])|array(array(url[, name])),
	 * 			'subject' => string,
	 * 			'body' => string|template_file,
	 * 			'params' => array(key => value)
	 * 		),
	 * 		'specifics' => array([each line is similar to the 'defaults' array])
	 * 	);
	 *
	 * @param array $params
	 * @return array|WNote Email status or WNote
	 */
	protected function send(array $params) {
		// Check if this application is called from another, if not return
		if (!$this->hasParent()) {
			return WNote::error('direct_access_not_allowed', 'direct_access_not_allowed');
		}

		// Check that $params is not empty
		if (empty($params) || !is_array($params)) {
			return WNote::error('missing_parameters', 'missing_parameters');
		}

		if (empty($this->configuration)) {
			$this->configuration = $this->model->getConfiguration();
		}

		$success = true;

		// Set the default response policy to 'from' if nothing is provided
		if (!empty($params['response_policy'])) {
			$this->response_policy = $params['response_policy'];
		} else {
			$this->response_policy = 'from';
		}

		// Set the default action expiration method if nothing is provided
		if (!empty($params['action_expiration'])) {
			$this->expiration = $params['action_expiration'];
		} else {
			$this->expiration = 'P30D';
		}

		// Generate uniqid and hash used to execute action from this mail
		$mailing_list_id = uniqid('mail', true);
		$this->hash_mailing_list = sha1($mailing_list_id);

		$this->phpmailer = WHelper::load("phpmailer");
		$this->phpmailer->CharSet = 'utf-8';
		$this->phpmailer->isHTML(true);

		// Find mode : only defaults or defaults + while(specifics)
		if (empty($params['specifics']) || !is_array($params['specifics'])) {
			foreach ($params['specifics'] as $spec) {
				$success = $success &&
					$this->sendMail(array_replace_recursive(
						array(),
						$params['defaults'],
						$spec
					));
			}
		} else {
			$success = $this->sendMail($params['defaults']);
		}

		unset($this->phpmailer);

		if (!$success) {
			return WNote::error('mail_send_error', 'mail_send_error');
		} else {
			return array('success' => true);
		}
	}

	private function sendEmail(array $params) {
		$success = true;

		// Prepare Template compiler
		if (empty($this->tpl)) {
			$this->tpl = WSystem::getTemplate();
		}

		$this->tpl->pushContext();

		// Clean the PHPMailer instance
		$this->phpmailer->clearAddresses();
		$this->phpmailer->clearAttachments();

		// Add addresses

		// If POP3/IMAP enabled, mail app will manage responses, 'to' goes to CC, a placeholder goes to 'to'
		if ($this->configuration['canReceive']) {
			$from = array($this->configuration['from']);

			if (is_array($params['from'] && !empty($params['from'][1]))) {
				$from[] = $params['from'][1];
			}

			// From: array('notifications@domain.com', 'Real from name')
			$this->addAddressesInField($from, 'from');

			// To: array('site@no-reply.domain.com', 'A previously configured name')
			$this->addAddressesInField($this->configuration['to'], 'to');

			$this->addAddressesInField($params['to'], 'cc');
			$this->addAddressesInField($params['cc'], 'cc');
			$this->addAddressesInField($params['bcc'], 'bcc');

			// Reply to: array('hash_mailing_list'.'hash_mail'@domain.com, 'A previously configured name')
			$this->addAddressesInField(array($this->getInternalResponseAddress(), $this->configuration['name']), 'replyTo');
		} else {
			// No POP3/IMAP, classical e-mail system
			$this->addAddressesInField($params['from'], 'from');
			$this->addAddressesInField($params['to'], 'to');
			$this->addAddressesInField($params['cc'], 'cc');
			$this->addAddressesInField($params['bcc'], 'bcc');
		}


		$params['params']['mail_app'] = array();

		// Generate hash used to execute action from this mail
		$this->hash_mail = sha1(serialize($params['to']).$this->hash_mailing_list);
		$params['params']['mail_app']['hash_mail'] = $this->hash_mail;

		// Assign View variables
		$this->tpl->assign($params['params']);

		// Generates
		$this->phpmailer->Subject = $this->tpl->parseString($params['subject']);

		if (substr($params['body'], -5) === '.html' && file_exists(WITY_PATH.$params['body'])) {
			// Use system directory separator
			if (DS != '/') {
				$params['body'] = str_replace('/', DS, $params['body']);
			}

			$this->phpmailer->msgHTML($this->tpl->parse($params['body']));
		} else {
			$this->phpmailer->msgHTML($this->tpl->parseString($params['body']));
		}

		if (!$this->phpmailer->send()) {
			// TODO Change WNote::error handler and translate it
			WNote::error('mail_send_fail', 'mail_send_fail: '.$this->phpmailer->ErrorInfo);
			$success = false;
		}

		/*if (!$this->model->addMail($params)) {
			WNote::error('unable_to_save_email', WLang::get('unable_to_save_email', serialize($params)), 'email');
			$success = false;
		}*/

		$this->tpl->popContext();

		return $success;
	}

	private function addAddressesInField($addresses, $type) {
		if (empty($addresses)) {
			return false;
		}

		$success = true;

		// Assign the right function to use with these addresses
		$func = $this->phpmailer->addAddress;

		switch ($type) {
			case 'to':
				$func = $this->phpmailer->addAddress;
				break;

			case 'cc':
				$func = $this->phpmailer->addCC;
				break;

			case 'bcc':
				$func = $this->phpmailer->addBCC;
				break;

			case 'from':
				$func = $this->phpmailer->setFrom;
				break;

			case 'replyTo':
				$func = $this->phpmailer->addReplyTo;
				break;

			default:
				$func = $this->phpmailer->addAddress;
				break;
		}

		if (!is_array($addresses)) {
			// 'email'
			$success = $func($addresses);
		} else if (!is_array($addresses[0])) {
			// array('email'[, 'name'])
			$success = call_user_func_array($func, $addresses);
		} else {
			// array(array('email'[,'name']))
			foreach ($addresses as $address) {
				$success = call_user_func_array($func, $address);
			}
		}

		// TODO Update WNote message
		WNote::warning('email_add_failure', 'email_add_failure: '.$this->phpmailer->ErrorInfo);

		return $success;
	}

	private function getInternalResponseAddress() {
		return $this->hash_mailing_list.'_'.$this->hash_mail.'@'.$this->configuration['domain'];
	}

	protected function redirect(array $params) {
		$this->setHeader('Location', WRoute::getDir());
	}

	/*****************************************
	 * WTemplateCompiler's new handlers part *
	 *****************************************/
	/**
	 * Handles the {mail_action} node in WTemplate
	 * {mail_action} gives access to email action
	 *
	 * Example: {action /news/admin/publish/{$news.id}}
	 * Replaced by: /mail/[hash]
	 *
	 * @param string $args language identifier
	 * @return string php string that calls the WLang::get()
	 */
	public static function compile_mail_action($args) {
		if (!empty($args)) {
			$url = array_shift($args);

			// Replace the template variables in the string
			$url = WTemplateParser::replaceNodes($url, create_function('$s', "return '\".'.WTemplateCompiler::parseVar(\$s).'.\"';"));

			// Build final php lang string
			if (strlen($url) > 0) {
				return '<?php echo MailController::storeAction("'.$url.'"); ?>';
			}
		}

		return '';
	}
}

?>
