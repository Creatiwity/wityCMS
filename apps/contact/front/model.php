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
	}
	
}

?>