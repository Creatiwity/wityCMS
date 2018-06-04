<?php
/**
 * Contact Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

// Include Front Model for inheritance
include_once APPS_DIR.'contact'.DS.'front'.DS.'model.php';

/**
 * ContactAdminModel is the Admin Model of the Contact Application
 *
 * @package Apps\Contact\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class ContactAdminModel extends ContactModel {

	public function __construct() {
		parent::__construct();

		// Declare table
		$this->db->declareTable('users');
	}

	/**
	 * Counts the emails in the database.
	 *
	 * @return array Number of emails stored
	 */
	public function getEmailCount() {
		$prep = $this->db->prepare('
			SELECT COUNT(*) FROM contact
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}

	/**
	 * Retrieves a list of emails.
	 *
	 * @param int    $from     Position of the first email to return
	 * @param int    $number   Number of emails
	 * @param string $order    Name of the ordering column
	 * @param bool   $asc      Ascendent or descendent?
	 * @return array A list of information about the emails found
	 */
	public function getEmailList($from, $number, $order = 'created_date', $asc = false) {
		$prep = $this->db->prepare('
			SELECT `contact`.`id`, `from`, `users`.`nickname` AS from_nickname, `to`, `name`, `organism`, `object`, `message`, `attachment`, `contact`.`created_date`
			FROM contact
			LEFT JOIN users
			ON from_id = users.id
			ORDER BY contact.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			'.($from >= 0 && $number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves informations about a specified email.
	 *
	 * @param int $emailid Id of the wanted email
	 * @return array Information about the email
	 */
	public function getEmail($emailid) {
		$prep = $this->db->prepare('
			SELECT `contact`.`id`, `from`, `users`.`nickname` AS from_nickname, `to`, `name`, `organism`, `object`, `message`, `attachment`, `contact`.`created_date`
			FROM contact
			LEFT JOIN users
			ON from_id = users.id
			WHERE contact.id = :emailid
		');
		$prep->bindParam(':emailid', $emailid, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Defines a config in contact_config table.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function setConfig($key, $value) {
		$prep = $this->db->prepare('
			UPDATE contact_config
			SET value = :value
			WHERE `key` = :key
		');
		$prep->bindParam(':key', $key);
		$prep->bindParam(':value', $value);
		return $prep->execute();
	}

}

?>
