<?php
/**
 * Settings Application - Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SettingsModel is the Model of the Settings Application
 * 
 * @package Apps\Settings\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.0.1-17-06-2015
 */
class SettingsAdminModel {

	/**
	 * @var WDatabase instance
	 */
	protected $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		// Declare table
		$this->db->declareTable('languages');
	}
	
	/**
	 * Retrieve languages from database
	 * 
	 * @return Array
	 */
	public function getLanguages() {
		$prep = $this->db->query('SELECT * FROM languages');

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieve a specific language from database
	 * 
	 * @param int $id
	 * @return Array
	 */
	public function getLanguage($id) {
		$prep = $this->db->prepare('SELECT * FROM languages WHERE id = ?');
		$prep->execute(array($id));

		return $prep->fetch();
	}

	/**
	 * Create a language
	 * 
	 * @param Array $data
	 * @return Bool success
	 */
	public function insertLanguage($data) {
		return $this->db->insertInto('languages', array('name', 'iso', 'code', 'date_format_short', 'date_format_long', 'enabled'), $data);
	}

	/**
	 * Update a language
	 * 
	 * @param Array $data
	 * @return Bool success
	 */
	public function updateLanguage($id, $data) {
		return $this->db->update('languages', array('name', 'iso', 'code', 'date_format_short', 'date_format_long', 'enabled'), $data, 'id = '.$id);
	}
}

?>
