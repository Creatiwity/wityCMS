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
 * @version 0.4.0-26-11-2013
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
		$results = $prep->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		$results = array_map('reset', $results);

		return $results;
	}
}
