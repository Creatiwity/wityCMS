<?php
/**
 * User Application - Admin Model - /apps/user/admin/model.php
 */

defined('IN_WITY') or die('Access denied');

// Include Front Model for inheritance
include_once APPS_DIR.'user'.DS.'front'.DS.'model.php';

/**
 * UserAdminModel is the admin Model of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-29-01-2013
 */
class UserAdminModel extends UserModel {
	private $db;
	
	public function __construct() {
		parent::__construct();
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Deletes a user
	 * 
	 * @param int $userid
	 * @todo Move this method to UserModel to propose account deletion by user
	 */
	public function deleteUser($userid) {
		$prep = $this->db->prepare('
			DELETE FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $userid, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Retrieves the list of user groups
	 * 
	 * @param string $order Name of the ordering column
	 * @param string $asc   Ascendent or descendent?
	 */
	public function getGroupsList($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT id, name, access
			FROM users_groups
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();
		return $prep->fetchAll();
	}
	
	/**
	 * Creates a new group in the database
	 * 
	 * @param array $data array(nickname, access)
	 */
	public function createGroup($data) {
		$prep = $this->db->prepare('
			INSERT INTO users_groups(name, access)
			VALUES (:name, :access)
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':access', $data['access']);
		return $prep->execute();
	}
	
	/**
	 * Updates a group
	 * 
	 * @param int    $gid   Group id
	 * @param array  $data  Columns to update
	 */
	public function updateGroup($gid, $data) {
		return $this->db->query('
			UPDATE users_groups
			SET name = '.$this->db->quote($data['nameEdit']).', access = '.$this->db->quote($data['accessEdit']).'
			WHERE id = '.$gid
		);
	}
	
	/**
	 * Updates a group in the database
	 * 
	 * @param int    $gid   Group id
	 */
	public function deleteCat($gid) {
		$prep = $this->db->prepare('
			DELETE FROM users_groups WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
}

?>