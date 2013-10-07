<?php
/**
 * Contact Application - Front Model - /apps/contact/front/model.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactModel is the Front Model of the Contact Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-02-10-2013
 */
class ContactModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		// Declare table
		$this->db->declareTable('contact');
		$this->db->declareTable('contact_config');
	}

	public function getConfig() {
		$prep = $this->db->prepare(
			'SELECT `key`, `value` 
			FROM `contact_config` 
			WHERE `key` = "site_from_email" 
				OR `key` = "site_from_name"');
		$prep->execute();
		return $prep->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
	}

	public function addMail(array $params) {
		$prep = $this->db->prepare(
			'INSERT INTO contact(`from`, `from_id`, `to`, `cc`, `bcc`, `reply_to`, `name`, `organism`, `object`, `message`, `date`) 
			VALUES (:from_email, :user_id, :to, :cc, :bcc, :reply_to, :from_name, :organism, :object, :message, NOW())');

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
		
		return $prep->execute();
	}
	
}

?>