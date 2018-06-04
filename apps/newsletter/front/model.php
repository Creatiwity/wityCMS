<?php
/**
 * Newsletter Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterModel is the Front Model of the Newsletter Application
 *
 * @package Apps\Newsletter\Front
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 0.6.2-04-06-2018
 */
class NewsletterModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('newsletter');
	}

	public function isEmailInDb($email) {
		$prep = $this->db->prepare('
			SELECT count(*)
			FROM newsletter
			WHERE email = :email
		');
		$prep->bindParam(':email', $email);
		$prep->execute();

		$rows = $prep->fetch(PDO::FETCH_NUM);

		return $rows[0] > 0;
	}

	/**
	 * Counts portfolio in the database
	 *
	 * @return int
	 */
	public function addEmail($email) {
		$prep = $this->db->prepare('
			INSERT INTO newsletter (email)
			VALUES (:email)
		');
		$prep->bindParam(':email', $email);

		return $prep->execute();
	}
}

?>
