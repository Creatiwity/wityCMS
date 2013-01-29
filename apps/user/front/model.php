<?php
/**
 * User Application - Model - /apps/user/front/model.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserModel is the front Model of the User Application
 *
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-29-01-2013
 */
class UserModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Checks whether a $userid truely exists in the database
	 * 
	 * @param string $userid
	 * @return boolean Only one row must be returned
	 */
	public function validId($userid) {
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $userid, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Checks whether a nickname is available
	 * 
	 * @param string $nikcname
	 * @return boolean
	 */
	public function nicknameAvailable($nickname) {
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE nickname LIKE :nickname
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->execute();
		return $prep->rowCount() == 0;
	}
	
	/**
	 * Checks whether an email is available
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public function emailAvailable($email) {
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE email LIKE :email
		');
		$prep->bindParam(':email', $email);
		$prep->execute();
		return $prep->rowCount() == 0;
	}
	
	/**
	 * Gets the id of the last user inserted
	 * Useful when creating a new user
	 * 
	 * @return int The id of the lattest user
	 */
	public function getLastUserId() {
		$prep = $this->db->prepare('
			SELECT id FROM users ORDER BY id DESC LIMIT 1
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	/**
	 * Counts the users in the database
	 * 
	 * @param array  $filters  List of criterias to add in the request (nickname, email, firstname, lastname and group)
	 * @return array A list of information about the users found
	 */
	public function countUsers(array $filters = array()) {
		if (empty($filters)) {
			$prep = $this->db->prepare('
				SELECT COUNT(*) FROM users
			');
		} else {
			$cond = '';
			$allowed = array('nickname', 'email', 'firstname', 'lastname');
			foreach ($filters as $name => $value) {
				if (!empty($value) && in_array($name, $allowed)) {
					$cond .= $name." LIKE '%".$value."%' AND ";
				}
			}
			if (!empty($filters['groupe'])) {
				$cond = 'LEFT JOIN users_groups
				ON groupe = users_groups.id
				WHERE '.$cond.'groupe = '.intval($filters['groupe']);
			} else {
				$cond = 'WHERE '.substr($cond, 0, -5);
			}
			
			$prep = $this->db->prepare('
				SELECT COUNT(*)
				FROM users
				'.$cond
			);
		}
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	/**
	 * Retrieves a list of users
	 * 
	 * @param int    $from     Position of the first user to return
	 * @param int    $number   Number of users
	 * @param string $order    Order column critera
	 * @param bool   $asc      Ascendent or descendent?
	 * @param array  $filters  List of criterias to add in the request (nickname, email, firstname, lastname and group)
	 * @return array A list of information about the users found
	 */
	public function getUsersList($from, $number, $order = 'nickname', $asc = true, array $filters = array()) {
		// Add filters
		$cond = '';
		if (!empty($filters)) {
			$cond = 'WHERE ';
			$allowed = array('nickname', 'email', 'firstname', 'lastname');
			foreach ($filters as $name => $value) {
				if (!empty($value) && in_array($name, $allowed)) {
					$cond .= $name.' LIKE "%'.$value.'%" AND ';
				}
			}
			if (!empty($filters['groupe'])) {
				$cond .= 'groupe = '.intval($filters['groupe']);
			} else {
				$cond = substr($cond, 0, -5);
			}
		}
		
		// Prepare request
		$prep = $this->db->prepare('
			SELECT users.id, nickname, email, firstname, lastname, country, users.access, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, DATE_FORMAT(last_activity, "%d/%m/%Y %H:%i") AS last_activity, ip
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			'.$cond.'
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		
		// Format data
		$data = array();
		while ($row = $prep->fetch(PDO::FETCH_ASSOC)) {
			$row['access'] = explode(',', $row['access']);
			$data[] = $row;
		}
		return $data;
	}
	
	/**
	 * Retrieves informations about a specified user
	 * 
	 * @param int $userid Id of the user wanted
	 * @return array Information about the user
	 */
	public function getUser($userid) {
		$prep = $this->db->prepare('
			SELECT nickname, email, firstname, lastname, country, groupe, users_groups.name, users.access
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $userid, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Finds a user in the database matching with $nickname and $password
	 * 
	 * @param string $nickname
	 * @param string $password
	 * @return array Information of the users found
	 */
	public function matchUser($nickname, $password) {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, firstname, lastname, country, groupe, access
			FROM users
			WHERE (nickname = :nickname OR email = :nickname) AND password = :password
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->bindParam(':password', $password);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Creates a user in the database
	 * 
	 * @param array $data
	 * @return boolean Request success
	 */
	public function createUser(array $data) {
		$prep = $this->db->prepare('
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, adresse, code_postal, ville, groupe, ip)
			VALUES (:nickname, :password, :confirm, :email, :firstname, :lastname, :adresse, :code_postal, :ville, :groupe, :ip)
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
		$prep->bindParam(':adresse', $data['adresse']);
		$prep->bindParam(':code_postal', $data['code_postal']);
		$prep->bindParam(':ville', $data['ville']);
		$prep->bindParam(':groupe', $data['groupe']);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute() or die($prep->errorInfo());
	}
	
	/**
	 * Updates a user in the database
	 * 
	 * @param id $userid  Id of the user
	 * @param array $data Informations to update
	 * @return boolean Request success
	 */
	public function updateUser($userid, array $data) {
		$string = '';
		foreach ($data as $key => $value) {
			$string .= $key.' = '.$this->db->quote($value).', ';
		}
		$string = substr($string, 0, -2);
		
		return $this->db->query('
			UPDATE users
			SET '.$string.'
			WHERE id = '.$userid
		);
	}
	
	/**
	 * Updates the last_activity timestamp and the ip of a user in the database
	 * 
	 * @param id $userid  Id of the user
	 * @return boolean Request success
	 */
	public function updateLastActivity($userid) {
		$prep = $this->db->prepare('
			UPDATE users
			SET last_activity = NOW(), ip = :ip
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $userid, PDO::PARAM_INT);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute();
	}
	
	/**
	 * Validates an account in the database
	 * 
	 * @param string $confirm The confirm code of the user
	 * @return boolean Request success
	 */
	public function validateAccount($confirm) {
		$prep = $this->db->prepare('
			UPDATE users
			SET confirm = ""
			WHERE confirm = :confirm
		');
		$prep->bindParam(':confirm', $confirm);
		return $prep->execute();
	}
}

?>
