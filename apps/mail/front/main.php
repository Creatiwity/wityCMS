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
 * @version 0.6.2-04-06-2018
 */
class MailController extends WController {

	/**
	 * @var PHPMailer phpMailer instance
	 */
	private $phpmailer;

	/**
	 * @var WTemplate WTemplate instance
	 */
	private static $tpl;

	/**
	 * @var Hash variable that represents current mailing list
	 */
	private static $hash_mailing_list;

	/**
	 * @var Hash variable that represents current mail
	 */
	private static $hash_mail;

	/**
	 * @var Link expiration method
	 */
	private static $expiration;

	/**
	 * @var Response mode, 'all' will answer to all, 'from' will answer to the from email field, 'none' is no-reply@...
	 */
	private static $response_policy;

	/**
	 * @var Configuration parameters
	 */
	private static $configuration;

	/**
	 * @var Static model instance for mail_action node compilation
	 */
	private static $smodel;

	/**
	 * Sends email
	 *
	 * 	$params = array(
	 * 		'origin' => array(
	 * 			'app' => 'app_name',
	 * 			'action' => 'action',
	 * 			'parameters' => array(...)
	 * 		),
	 * 		'response_policy' => 'all'|'from' (default)|'none',
	 * 		'action_expiration' => 'one-time-action'|'one-time-mail'|DateInterval initializer ('P30D' default)|array('one-time-action'|'one-time-mail', DateInterval initializer),
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

		if (empty(self::$configuration)) {
			self::$configuration = $this->model->getConfiguration();
		}

		$success = true;

		// Set the default response policy to 'from' if nothing is provided
		if (!empty($params['response_policy'])) {
			self::$response_policy = $params['response_policy'];
		} else {
			self::$response_policy = 'from';
		}

		// Set the default action expiration method if nothing is provided
		if (!empty($params['action_expiration'])) {
			if ($params['action_expiration'] == 'one-time-mail') {

				// 'one-time-mail'
				self::$expiration = array('expires' => '', 'one-time' => 'M');

			} else if ($params['action_expiration'] == 'one-time-action') {

				// 'one-time-action'
				self::$expiration = array('expires' => '', 'one-time' => 'A');

			} else if (is_array($params['action_expiration'])) {

				// array(...)
				if ($params['action_expiration'][0] == 'one-time-mail') {

					// array('one-time-mail', DateInterval initializer)
					self::$expiration = array('expires' => '', 'one-time' => 'M');

				} else if ($params['action_expiration'][0] == 'one-time-action') {

					// array('one-time-action', DateInterval initializer)
					self::$expiration = array('expires' => '', 'one-time' => 'A');

				}

				$expires_date = new WDate();
				$expires_date->add(new DateInterval($params['action_expiration'][1]));
				self::$expiration = array('expires' => $expires_date, 'one-time' => '');
			} else {

				// DateInterval initializer
				$expires_date = new WDate();
				$expires_date->add(new DateInterval($params['action_expiration']));
				self::$expiration = array('expires' => $expires_date, 'one-time' => '');
			}
		} else {

			// Default: 'P30D' (DateInterval initializer)
			$expires_date = new WDate();
			$expires_date->add(new DateInterval('P30D'));
			self::$expiration = array('expires' => $expires_date, 'one-time' => '');

		}

		if (empty($params['response_callback'])) {
			$params['response_callback'] = '';
		}

		// Generate uniqid and hash used to execute action from this mail
		self::$hash_mailing_list = uniqid('mail', true);

		$this->model->addMailing(
			self::$hash_mailing_list,
			self::$expiration,
			self::$response_policy,
			$params['response_callback'],
			$params['origin']['app'],
			$params['origin']['action'],
			$params['origin']['parameters']
		);

		$this->phpmailer = WHelper::load("phpmailer");
		$this->phpmailer->CharSet = 'utf-8';
		$this->phpmailer->isHTML(true);

		WTemplateCompiler::registerCompiler('mail_action', array('MailController', 'compile_mail_action'));

		// Stores hashes relative to a particular email (same order as $params['specifics'])
		$specifics_hashes = array();

		// Find mode : only defaults or defaults + while(specifics)
		if (!empty($params['specifics']) && is_array($params['specifics'])) {
			foreach ($params['specifics'] as $spec) {
				$success = $success &&
					$this->sendEmail(
					array_replace_recursive(
						array(
							'from' => '',
							'to' => '',
							'cc' => '',
							'bcc' => '',
							'attachments' => array(),
							'subject' => '',
							'body' => '',
							'params' => array()
						),
						$params['defaults'],
						$spec
					), $specifics_hashes
				);
			}
		} else {
			$success = $this->sendEmail(
				array_replace_recursive(
					array(
						'from' => '',
						'to' => '',
						'cc' => '',
						'bcc' => '',
						'attachments' => array(),
						'subject' => '',
						'body' => '',
						'params' => array()
					),
					$params['defaults']
					),
				$specifics_hashes
			);
		}

		WTemplateCompiler::unregisterCompiler('mail_action');

		unset($this->phpmailer);

		if (!$success) {
			return WNote::error('mail_send_error', 'mail_send_error');
		} else {
			return array(
				'success' => true,
				'hashes' => $specifics_hashes
			);
		}
	}

	private function sendEmail(array $params, &$specifics_hashes) {
		$success = true;

		// Prepare Template compiler
		if (empty(self::$tpl)) {
			self::$tpl = WSystem::getTemplate();
		}

		self::$tpl->pushContext();

		// Clean the PHPMailer instance
		$this->phpmailer->clearAllRecipients();
		$this->phpmailer->clearAttachments();

		// Add addresses

		// If POP3/IMAP enabled, mail app will manage responses, 'to' goes to CC, a placeholder goes to 'to'
		if (self::$configuration['canReceive'] == '1') {
			$from = array(self::$configuration['from']);

			if (is_array($params['from'] && !empty($params['from'][1]))) {
				$from[] = $params['from'][1];
			}

			// From: array('notifications@domain.com', 'Real from name')
			$this->addAddressesInField($from, 'from');

			// To: array('site@no-reply.domain.com', 'A previously configured name')
			$this->addAddressesInField(self::$configuration['to'], 'to');

			$this->addAddressesInField($params['to'], 'cc');
			$this->addAddressesInField($params['cc'], 'cc');
			$this->addAddressesInField($params['bcc'], 'bcc');

			// Reply to: array('hash_mailing_list'.'hash_mail'@domain.com, 'A previously configured name')
			$this->addAddressesInField(array($this->getInternalResponseAddress(), self::$configuration['name']), 'replyTo');
		} else {
			// No POP3/IMAP, classical e-mail system
			$this->addAddressesInField($params['from'], 'from');
			$this->addAddressesInField($params['to'], 'to');
			$this->addAddressesInField($params['cc'], 'cc');
			$this->addAddressesInField($params['bcc'], 'bcc');
		}

		if (!empty($params['attachments']) && is_array($params['attachments'])) {
			$func = array($this->phpmailer, 'addAttachment');

			if (!is_array($params['attachments'][0])) {
				// array('email'[, 'name'])
				call_user_func_array($func, $params['attachments']);
			} else {
				// array(array('email'[,'name']))
				foreach ($params['attachments'] as $attachment) {
					call_user_func_array($func, $attachment);
				}
			}
		}

		$params['params']['mail_app'] = array();

		// Generate hash used to execute action from this mail
		while(!isset(self::$hash_mail) || $this->model->hashMailExists(self::$hash_mail)) {
			$salt_part = rand();
			self::$hash_mail = sha1(serialize($params['to']).self::$hash_mailing_list.'?*'.$salt_part);
		}

		$specifics_hashes[] = self::$hash_mail;

		$params['params']['mail_app']['hash_mail'] = self::$hash_mail;

		// Assign View variables
		self::$tpl->assign($params['params']);

		// Generates
		$this->phpmailer->Subject = self::$tpl->parseString($params['subject']);

		if (substr($params['body'], -5) === '.html' && file_exists(WITY_PATH.$params['body'])) {
			// Use system directory separator
			if (DS != '/') {
				$params['body'] = str_replace('/', DS, $params['body']);
			}

			$this->phpmailer->msgHTML(self::$tpl->parse($params['body']));
		} else {
			$this->phpmailer->msgHTML(self::$tpl->parseString($params['body']));
		}

		if (!$this->phpmailer->send()) {
			// TODO Change WNote::error handler and translate it
			WNote::error('mail_send_fail', 'mail_send_fail: '.$this->phpmailer->ErrorInfo, 'email, log');
			$success = false;
		}

		if (!$this->model->addMail(
			self::$hash_mail,
			self::$hash_mailing_list,
			$params['from'],
			$params['to'],
			$params['cc'],
			$params['bcc'],
			$params['attachments'],
			$params['subject'],
			$params['body'],
			$this->phpmailer->Subject,
			$this->phpmailer->Body,
			$params['params']
		)) {
			WNote::error('email_not_saved', 'email_not_saved', 'email, log');
			$success = false;
		}

		self::$tpl->popContext();

		return $success;
	}

	private function addAddressesInField($addresses, $type) {
		if (empty($addresses)) {
			return false;
		}

		$success = true;

		// Assign the right function to use with these addresses
		$func = array($this->phpmailer, 'addAddress');

		switch ($type) {
			case 'to':
				$func[1] = 'addAddress';
				break;

			case 'cc':
				$func[1] = 'addCC';
				break;

			case 'bcc':
				$func[1] = 'addBCC';
				break;

			case 'from':
				$func[1] = 'setFrom';
				break;

			case 'replyTo':
				$func[1] = 'addReplyTo';
				break;

			default:
				$func[1] = 'addAddress';
				break;
		}

		if (!is_array($addresses)) {
			// 'email'
			$success = call_user_func($func, $addresses);
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
		if (!$success) {
			WNote::error('email_add_failure', 'email_add_failure: '.$this->phpmailer->ErrorInfo);
		}

		return $success;
	}

	private function getInternalResponseAddress() {
		return self::$hash_mail.'@'.self::$configuration['domain'];
	}

	protected function redirect(array $params) {
		$location = WRoute::getDir();

		if (isset($params) && !empty($params[0])) {
			$location .= $this->model->getActionURL($params[0]);
		}

		$this->setHeader('Location', $location);
	}

	public static function storeAction($url) {
		if (empty($url)) {
			return '';
		}

		if (empty(self::$smodel)) {
			self::$smodel = new MailModel();
		}

		// Generate hash used to execute action from this mail
		while(!isset($hash_action) || self::$smodel->hashActionExists($hash_action)) {
			$salt_part = rand();
			$hash_action = sha1($url.self::$hash_mail.'?*'.$salt_part);
		}

		if (self::$smodel->addAction($hash_action, self::$hash_mail, self::$expiration['one-time'], self::$expiration['expires'], $url)) {
			return WRoute::getBase().'mail/redirect/'.$hash_action;
		} else {
			return '';
		}
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
			$url = $args;

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
