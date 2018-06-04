<?php
/**
 * Mail Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * MailModel is the Front Model of the Mail Application
 *
 * @package Apps\Mail\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class MailModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('mail_mailing');
		$this->db->declareTable('mail_list');
		$this->db->declareTable('mail_available_actions');
		$this->db->declareTable('mail_action_history');
		$this->db->declareTable('mail_configuration');
	}

	public function getConfiguration() {
		$prep = $this->db->prepare('
			SELECT `key`, `value`
			FROM mail_configuration
		');
		$prep->execute();
		$results = $prep->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		$results = array_map('reset', $results);

		return $results;
	}

	public function addMailing($id, $action_expiration, $response_policy, $response_callback, $origin_app, $origin_action, $origin_parameters) {

		if (isset($_SESSION['userid'])) {
			$sender_id = $_SESSION['userid'];
		} else {
			$sender_id = 0;
		}

		$prep = $this->db->prepare('
			INSERT INTO mail_mailing(internal_id, action_expiration, response_policy, response_callback, sender_id, origin_app, origin_action, origin_parameters)
			VALUES (:internal_id, :action_expiration, :response_policy, :response_callback, :sender_id, :origin_app, :origin_action, :origin_parameters)
		');
		$prep->bindParam(':internal_id', $id);
		$_action_expiration = serialize($action_expiration);
		$prep->bindParam(':action_expiration', $_action_expiration);
		$prep->bindParam(':response_policy', $response_policy);
		$prep->bindParam(':response_callback', $response_callback);
		$prep->bindParam(':sender_id', $sender_id);
		$prep->bindParam(':origin_app', $origin_app);
		$prep->bindParam(':origin_action', $origin_action);
		$_origin_parameters = serialize($origin_parameters);
		$prep->bindParam(':origin_parameters', $_origin_parameters);

		return $prep->execute();
	}

	public function addMail($hash, $mailing_hash_id, $from, $to, $cc, $bcc, $attachments, $subject, $body, $compiled_subject, $compiled_body, $params) {

		$prep = $this->db->prepare('
			INSERT INTO mail_list(hash, mailing_hash_id, `from`, `to`, cc, bcc, attachments, subject, body, compiled_subject, compiled_body, params, state, date_state_modified)
			VALUES (:hash, :mailing_hash_id, :from, :to, :cc, :bcc, :attachments, :subject, :body, :compiled_subject, :compiled_body, :params, :state, NOW())
		');

		$prep->bindParam(':hash', $hash);
		$prep->bindParam(':mailing_hash_id', $mailing_hash_id);
		$_form = serialize($from);
		$prep->bindParam(':from', $_form);
		$_to = serialize($to);
		$prep->bindParam(':to', $_to);
		$_cc = serialize($cc);
		$prep->bindParam(':cc', $_cc);
		$_bcc = serialize($bcc);
		$prep->bindParam(':bcc', $_bcc);
		$_attachments = serialize($attachments);
		$prep->bindParam(':attachments', $_attachments);
		$prep->bindParam(':subject', $subject);
		$prep->bindParam(':body', $body);
		$prep->bindParam(':compiled_subject', $compiled_subject);
		$prep->bindParam(':compiled_body', $compiled_body);
		$_params = serialize($params);
		$prep->bindParam(':params', $_params);
		$state = 'SENT';
		$prep->bindParam(':state', $state);

		return $prep->execute();
	}

	private function actionMail($hash) {
		$prep = $this->db->prepare('
			UPDATE mail_list
			SET state = "RESPONDED", date_state_modified = NOW()
			WHERE hash = :hash
		');

		$prep->bindParam(':hash', $hash);

		return $prep->execute();
	}

	public function hashMailExists($hash) {
		$prep = $this->db->prepare('
			SELECT COUNT(id)
			FROM mail_list
			WHERE hash = :hash
		');
		$prep->bindParam(':hash', $hash);
		$prep->execute();

		return $prep->fetchColumn() > 0;
	}

	public function hashActionExists($hash) {
		$prep = $this->db->prepare('
			SELECT COUNT(id)
			FROM mail_available_actions
			WHERE hash_action = :hash
		');
		$prep->bindParam(':hash', $hash);
		$prep->execute();

		return $prep->fetchColumn() > 0;
	}

	public function addAction($hash_action, $hash_mail, $one_time, $expires, $url) {
		$prep = $this->db->prepare('
			INSERT INTO mail_available_actions(hash_action, hash_mail, one_time, expires, url)
			VALUES (:hash_action, :hash_mail, :one_time, :expires, :url)
		');
		$prep->bindParam(':hash_action', $hash_action);
		$prep->bindParam(':hash_mail', $hash_mail);
		$prep->bindParam(':one_time', $one_time);
		$prep->bindParam(':expires', $expires);
		$prep->bindParam(':url', $url);

		return $prep->execute();
	}

	public function cleanExpiredActions() {
		$prep = $this->db->prepare('
			DELETE FROM mail_available_actions
			WHERE (expires < NOW() AND expires != 0) OR (expires = 0 AND one_time = "")
		');
		return $prep->execute();
	}

	private function deleteAction($hash) {
		$prep = $this->db->prepare('
			DELETE FROM mail_available_actions
			WHERE hash_action = :hash
		');
		$prep->bindParam(':hash', $hash);
		return $prep->execute();
	}

	private function deleteMailActions($hash) {
		$prep = $this->db->prepare('
			DELETE FROM mail_available_actions
			WHERE hash_mail = :hash
		');
		$prep->bindParam(':hash', $hash);
		return $prep->execute();
	}

	private function storeExecutedAction($params) {
		if (isset($_SESSION['userid'])) {
			$userid = $_SESSION['userid'];
		} else {
			$userid = 0;
		}

		if (isset($_SESSION['email'])) {
			$email = $_SESSION['email'];
		} else {
			$email = '';
		}

		$prep = $this->db->prepare('
			INSERT INTO mail_action_history(hash_action, hash_mail, user_id, email, url)
			VALUES (:hash_action, :hash_mail, :user_id, :email, :url)
		');
		$prep->bindParam(':hash_action', $params['hash_action']);
		$prep->bindParam(':hash_mail', $params['hash_mail']);
		$prep->bindParam(':user_id', $userid);
		$prep->bindParam(':email', $email);
		$prep->bindParam(':url', $params['url']);

		return $prep->execute();
	}

	public function getActionURL($hash) {
		$this->cleanExpiredActions();

		$prep = $this->db->prepare('
			SELECT hash_action, hash_mail, one_time, expires, url
			FROM mail_available_actions
			WHERE hash_action = :hash
		');
		$prep->bindParam(':hash', $hash);
		$prep->execute();

		$result = $prep->fetch(PDO::FETCH_ASSOC);

		if ($result != false) {
			if ($result['one_time'] == 'A') {
				$this->deleteAction($result['hash_action']);
			} else if ($result['one_time'] == 'M') {
				$this->deleteMailActions($result['hash_mail']);
			}

			$this->storeExecutedAction($result);
			$this->actionMail($result['hash_mail']);

			$prepend = $result['url'];
			$append = '';

			if (strrpos($result['url'], '#')) {
				$url_temp = explode('#', $result['url']);
				$prepend = $url_temp[0];
				$append = '#'.$url_temp[1];
			}

			if (!strrpos($prepend, '?')) {
				$prepend .= '?';
			} else {
				$prepend .= '&';
			}

			return ($prepend.'hash_mail='.$result['hash_mail'].'&hash_action='.$result['hash_action'].$append);
		}

		return '';
	}
}
