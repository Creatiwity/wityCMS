<?php
/**
 * Contact Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * ContactModel is the Front Model of the Contact Application
 *
 * @package Apps\Contact\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class ContactModel {

	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare tables
		$this->db->declareTable('contact');
		$this->db->declareTable('contact_config');
	}

	/**
	 * Retrieves the contact's configuration from the database.
	 *
	 * @return array Array with two values: site_from_email and site_from_name
	 */
	public function getConfig() {
		$prep = $this->db->prepare('
			SELECT `key`, `value`
			FROM `contact_config`
			WHERE `key` = "site_from_email"
				OR `key` = "site_from_name"
		');
		$prep->execute();

		$config = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			if (!empty($data['key'])) {
				$config[$data['key']] = $data['value'];
			}
		}
		return $config;
	}

	/**
	 * Saves a contact request in the database.
	 *
	 * @param array $params Data of the contact request
	 * @return bool
	 */
	public function addMail(array $params) {
		$prep = $this->db->prepare('
			INSERT INTO contact(`from`, `from_id`, `to`, `cc`, `bcc`, `reply_to`, `name`, `organism`, `object`, `message`, `attachment`)
			VALUES (:from_email, :user_id, :to, :cc, :bcc, :reply_to, :from_name, :organism, :object, :message, :attachment)
		');

		$to = serialize($params['to']);
		$cc = isset($params['cc']) ? serialize($params['cc']) : serialize('');
		$bcc = isset($params['bcc']) ? serialize($params['bcc']) : serialize('');
		$replyTo = isset($params['reply_to']) ? serialize($params['reply_to']) : serialize('');

		$prep->bindParam(':from_email', $params['from_email']);
		$prep->bindParam(':user_id', $params['userid']);
		$prep->bindParam(':to', $to);
		$prep->bindParam(':cc', $cc);
		$prep->bindParam(':bcc', $bcc);
		$prep->bindParam(':reply_to', $replyTo);
		$prep->bindParam(':from_name', $params['from_name']);
		$prep->bindParam(':organism', $params['from_company']);
		$prep->bindParam(':object', $params['email_subject']);
		$prep->bindParam(':message', $params['email_message']);
		$prep->bindParam(':attachment', $params['attachment']);

		return $prep->execute();
	}

}

?>
