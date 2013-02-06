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
	 * Creates a user in the database
	 * This is a admin version able to change users.access
	 * 
	 * @param array $data
	 * @return boolean Request success
	 */
	public function createUser(array $data) {
		$prep = $this->db->prepare('
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, country, groupe, access, ip)
			VALUES (:nickname, :password, :confirm, :email, :firstname, :lastname, :country, :groupe, :access, :ip)
		');
		$prep->bindParam(':nickname', $data['nickname']);
		$prep->bindParam(':password', $data['password']);
		$confirm = isset($data['confirm']) ? $data['confirm'] : '';
		$prep->bindParam(':confirm', $confirm);
		$prep->bindParam(':email', $data['email']);
		$firstname = isset($data['firstname']) ? $data['firstname'] : '';
		$prep->bindParam(':firstname', $firstname);
		$lastname = isset($data['lastname']) ? $data['lastname'] : '';
		$prep->bindParam(':lastname', $lastname);
		$country = isset($data['country']) ? $data['country'] : '';
		$prep->bindParam(':country', $country);
		$prep->bindParam(':groupe', $data['groupe']);
		$prep->bindParam(':access', $data['access']);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute();
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
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves the list of user groups with users_count row
	 * 
	 * @param string $order Name of the ordering column
	 * @param string $asc   Ascendent or descendent?
	 */
	public function getGroupsListWithCount($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT users_groups.id, name, users_groups.access, COUNT(*) AS users_count, groupe
			FROM users
			RIGHT JOIN users_groups
			ON groupe = users_groups.id
			GROUP BY users_groups.id
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();
		$data = array();
		while ($row = $prep->fetch(PDO::FETCH_ASSOC)) {
			if ($row['groupe'] == 0) {
				$row['users_count'] = 0;
			}
			unset($row['groupe']);
			$data[] = $row;
		}
		return $data;
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
	 * Updates a group in the database
	 * 
	 * @param int    $groupid  Group id
	 * @param array  $data     Columns to update
	 */
	public function updateGroup($groupid, $data) {
		$prep = $this->db->prepare('
			UPDATE users_groups
			SET name = :name, access = :access
			WHERE id = :id
		');
		$prep->bindParam(':id', $groupid, PDO::PARAM_INT);
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':access', $data['access']);
		return $prep->execute();
	}
	
	/**
	 * Deletes a group in the database
	 * 
	 * @param int    $groupid  Group id
	 */
	public function deleteGroup($groupid) {
		$prep = $this->db->prepare('
			DELETE FROM users_groups WHERE id = :id
		');
		$prep->bindParam(':id', $groupid, PDO::PARAM_INT);
		return $prep->execute();
	}
}

?>