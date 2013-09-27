<?php
/**
 * User Application - Model - /apps/user/front/model.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserModel is the front Model of the User Application.
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-15-02-2013
 */
class UserModel {
	protected $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		// Declare table
		$this->db->declareTable('users');
		$this->db->declareTable('users_config');
		$this->db->declareTable('users_groups');
	}
	
	/**
	 * Checks whether a $userid truely exists in the database
	 * 
	 * @param string $userid
	 * @return boolean Only one row must be returned
	 */
	public function validId($user_id) {
		if (empty($user_id)) {
			return false;
		}
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $user_id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Checks whether a $userid truely exists in the database
	 * 
	 * @param string $userid
	 * @return boolean Only one row must be returned
	 */
	public function validGroupId($group_id) {
		if (empty($group_id)) {
			return false;
		}
		$prep = $this->db->prepare('
			SELECT * FROM users_groups WHERE id = :group_id
		');
		$prep->bindParam(':group_id', $group_id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Checks whether a nickname is valid and available
	 * 
	 * @param string $nikcname
	 * @return mixed true if valid or error string
	 */
	public function checkNickname($nickname) {
		if (empty($nickname) || strlen($nickname) < 3 || strlen($nickname) > 30) {
			return 'nickname_bad_length';
		} else if (preg_match('#[\.]+#', $nickname)) {
			return 'nickname_invalid_char';
		}
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE nickname LIKE :nickname
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->execute();
		if ($prep->rowCount() == 0) {
			return true;
		} else {
			return 'nickname_already_used';
		}
	}
	
	/**
	 * Checks whether an email is valid and available
	 * 
	 * @param string $email
	 * @return mixed true if valid or error string
	 */
	public function checkEmail($email) {
		if (empty($email) || !preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $email)) {
			return 'email_not_valid';
		}
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE email LIKE :email
		');
		$prep->bindParam(':email', $email);
		$prep->execute();
		if ($prep->rowCount() == 0) {
			return true;
		} else {
			return 'email_already_used';
		}
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
	 * @param array  $filters  List of criterias to add in the request (nickname, email, firstname, lastname and groupe)
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
				if (in_array($name, $allowed)) {
					if (strpos($value, '%') === false) {
						$value = '%'.$value.'%';
					}
					$cond .= $name." LIKE ".$this->db->quote($value)." AND ";
				}
			}
			if (isset($filters['valid'])) {
				$cond .= 'valid = '.intval($filters['valid']).' AND ';
			} else {
				$cond .= 'valid = 1 AND ';
			}
			if (!empty($filters['groupe'])) {
				$cond = 'LEFT JOIN users_groups
				ON groupe = users_groups.id
				WHERE '.$cond.'groupe = '.intval($filters['groupe']);
			} else if (!empty($cond)) {
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
	 * @param string $order    Name of the ordering column
	 * @param bool   $asc      Ascendent or descendent?
	 * @param array  $filters  List of criterias to add in the request (nickname, email, firstname, lastname and groupe)
	 * @return array A list of information about the users found
	 */
	public function getUsersList($from, $number, $order = 'nickname', $asc = true, array $filters = array()) {
		// Add filters
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('nickname', 'email', 'firstname', 'lastname');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, '%') === false) {
						$value = '%'.$value.'%';
					}
					$cond .= $name." LIKE ".$this->db->quote($value)." AND ";
				}
			}
			if (isset($filters['valid'])) {
				$cond .= 'valid = '.intval($filters['valid']).' AND ';
			} else {
				$cond .= 'valid = 1 AND ';
			}
			if (!empty($filters['groupe'])) {
				$cond .= 'groupe = '.intval($filters['groupe']).' AND ';
			}
			if (!empty($cond)) {
				$cond = 'WHERE '.substr($cond, 0, -5);
			}
		}
		
		$prep = $this->db->prepare('
			SELECT users.id, nickname, email, firstname, lastname, country, users.access, groupe, name AS groupe_name, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, DATE_FORMAT(last_activity, "%d/%m/%Y %H:%i") AS last_activity, ip
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			'.$cond.'
			ORDER BY users.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			'.($from != 0 && $number != 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves informations about a specified user
	 * 
	 * @param int $userid Id of the user wanted
	 * @return array Information about the user
	 */
	public function getUser($userid) {
		static $prep;
		if (empty($prep)) {
			$prep = $this->db->prepare('
				SELECT users.id, nickname, password, email, firstname, lastname, country, groupe, users_groups.name, users.access AS access, valid
				FROM users
				LEFT JOIN users_groups
				ON groupe = users_groups.id
				WHERE users.id = :userid
			');
		}
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
			SELECT id, nickname, password, email, firstname, lastname, country, groupe, access
			FROM users
			WHERE (nickname = :nickname OR email = :nickname) AND password = :password AND valid = 1
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
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, country, groupe, valid, ip)
			VALUES (:nickname, :password, :confirm, :email, :firstname, :lastname, :country, :groupe, :valid, :ip)
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
		$groupe = isset($data['groupe']) ? $data['groupe'] : '';
		$prep->bindParam(':groupe', $groupe);
		$valid = isset($data['valid']) ? $data['valid'] : 1;
		$prep->bindParam(':valid', $valid);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute();
	}
	
	/**
	 * Updates a user in the database
	 * 
	 * @param id $userid  Id of the user
	 * @param array $data Informations to update
	 * @return boolean Request success
	 */
	public function updateUser($userid, array $data) {
		if (empty($data)) {
			return true;
		}
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
	 * Find a user with its confirm code
	 * 
	 * @param string $confirm The confirm code of the user
	 * @return array User data (array() if not found)
	 */
	public function findUserWithConfirmCode($confirm) {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, firstname, lastname, country, groupe, access
			FROM users
			WHERE confirm = :confirm AND valid = 0
		');
		$prep->bindParam(':confirm', $confirm);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Find a user with its email
	 * 
	 * @param string $email Email of the user desired
	 * @return array User data (array() if not found)
	 */
	public function findUserWithEmail($email) {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, firstname, lastname, country, groupe, access
			FROM users
			WHERE email = :email AND valid = 1
		');
		$prep->bindParam(':email', $email);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Find a user with its email and confirm code
	 * 
	 * @param string $email    Email of the user desired
	 * @param string $confirm  Confirm code
	 * @return array User data (array() if not found)
	 */
	public function findUserWithEmailAndConfirmCode($email, $confirm) {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, firstname, lastname, country, groupe, access
			FROM users
			WHERE email = :email AND confirm = :confirm AND valid = 1
		');
		$prep->bindParam(':email', $email);
		$prep->bindParam(':confirm', $confirm);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Send an email in html for user app purpose
	 * 
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 */
	public function sendEmail($to, $subject, $body) {
		$mail = WHelper::load('phpmailer');
		$mail->CharSet = 'utf-8';
		$mail->From = WConfig::get('config.email');
		$mail->FromName = WConfig::get('config.site_name');
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->IsHTML(true);
		$mail->AddAddress($to);
		$mail->Send();
		unset($mail);
	}
	
	/**
	 * Retrieves the User app configuration stored in the users_config table
	 * 
	 * @return array
	 */
	public function getConfig() {
		$prep = $this->db->prepare('
			SELECT name, value
			FROM users_config
		');
		$prep->execute();
		$config = array();
		while ($row = $prep->fetch(PDO::FETCH_ASSOC)) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}
}

?>
