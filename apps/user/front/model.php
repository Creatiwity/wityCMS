<?php
/**
 * User Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserModel is the Front Model of the User Application.
 *
 * @package Apps\User\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
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
	 * Checks whether a nickname is valid and available
	 *
	 * @param string $nickname
	 * @return mixed true if valid or error string
	 */
	public function checkNickname($nickname) {
		if (empty($nickname) || strlen($nickname) < 3 || strlen($nickname) > 200) {
			return WLang::get('The nickname must contain between 3 and 30 characters.');
		} else if (!WTools::isEmail($nickname) && preg_match('#[\.]+#', $nickname)) {
			return WLang::get('The nickname contains invalid characters [.].');
		}

		$prep = $this->db->prepare('
			SELECT * FROM users WHERE nickname LIKE :nickname
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->execute();

		if ($prep->rowCount() == 0) {
			return true;
		} else {
			return WLang::get('The nickname is already in use.');
		}
	}

	/**
	 * Checks whether an email is valid and available
	 *
	 * @param string $email
	 * @return mixed true if valid or error string
	 */
	public function checkEmail($email) {
		if (!WTools::isEmail($email)) {
			return WLang::get('Please, provide a valid email.');
		}

		$prep = $this->db->prepare('
			SELECT * FROM users WHERE email LIKE :email
		');
		$prep->bindParam(':email', $email);
		$prep->execute();

		if ($prep->rowCount() == 0) {
			return true;
		} else {
			return WLang::get('The email is already in use.');
		}
	}

	/**
	 * Counts the users in the database.
	 *
	 * @param array $filters List of criteria to filter the request (nickname, email, firstname, lastname and groupe)
	 * @return int Number of users
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
	 * Retrieves a list of users.
	 *
	 * @param int    $from     Position of the first user to return
	 * @param int    $number   Number of users
	 * @param string $order    Name of the ordering column
	 * @param bool   $sens     Order: "ASC" or "DESC"
	 * @param array  $filters  List of criteria to filter the request (nickname, email, firstname, lastname and groupe)
	 * @return array A list of information about the users found
	 */
	public function getUsersList($from, $number, $order = 'nickname', $sens = 'ASC', array $filters = array()) {
		if (strtoupper($sens) != 'ASC') {
			$sens = 'DESC';
		}

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
			SELECT users.id, nickname, email, firstname, lastname, country, lang, groupe,
				users.access, valid, ip, name AS groupe_name, last_activity, users.created_date
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			'.$cond.'
			ORDER BY users.'.$order.' '.$sens.'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves informations about for specific user.
	 *
	 * @param int $user_id ID of the user
	 * @return array Information about the user
	 */
	public function getUser($user_id) {
		$prep = $this->db->prepare('
			SELECT users.id, nickname, password, email, firstname, lastname, country, lang, groupe,
				users.access, valid, ip, name AS groupe_name, last_activity, users.created_date
			FROM users
			LEFT JOIN users_groups
			ON groupe = users_groups.id
			WHERE users.id = :userid
		');
		$prep->bindParam(':userid', $user_id, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Finds a user in the database matching with $identifier and $password.
	 *
	 * @param string $identifier
	 * @param string $password
	 * @return array Information of the users found
	 */
	public function matchUser($identifier, $password) {
		$prep = $this->db->prepare('
			SELECT id, nickname, password, email, firstname, lastname, country, lang, groupe, access
			FROM users
			WHERE (nickname = :nickname OR email = :email) AND password = :password AND valid = 1
		');
		$prep->bindParam(':nickname', $identifier);
		$prep->bindParam(':email', $identifier);
		$prep->bindParam(':password', $password);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Creates a user in the database.
	 *
	 * @param array $data
	 * @return mixed ID of the user just created or false on failure
	 */
	public function createUser(array $data) {
		$prep = $this->db->prepare('
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, country, lang, groupe, valid, ip)
			VALUES (:nickname, :password, :confirm, :email, :firstname, :lastname, :country, :lang, :groupe, :valid, :ip)
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
		$lang = isset($data['lang']) ? $data['lang'] : '';
		$prep->bindParam(':lang', $lang);
		$groupe = isset($data['groupe']) ? $data['groupe'] : '';
		$prep->bindParam(':groupe', $groupe);
		$valid = isset($data['valid']) ? $data['valid'] : 1;
		$prep->bindParam(':valid', $valid);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);

		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * Updates a user in the database.
	 *
	 * @param int   $user_id ID of the user
	 * @param array $data    New data to assign
	 * @return bool Request status
	 */
	public function updateUser($user_id, array $data) {
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
			WHERE id = '.$user_id
		);
	}

	/**
	 * Updates the last_activity timestamp and the ip of a user in the database.
	 *
	 * @param int $user_id ID of the user
	 * @return bool Request status
	 */
	public function updateLastActivity($user_id) {
		$prep = $this->db->prepare('
			UPDATE users
			SET last_activity = NOW(), ip = :ip
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $user_id, PDO::PARAM_INT);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute();
	}

	/**
	 * Find a user with its confirm code.
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
	 * Find a user with its email.
	 *
	 * @param string $email Email of the user
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
	 * Find a user with its email and confirm code.
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
	 * Send an email in html for user app purpose.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 */
	public function sendEmail($to, $subject, $body) {
		$mail = WHelper::load('phpmailer');
		$mail->CharSet = 'utf-8';
		$mail->From = WConfig::get('config.email');
		$mail->FromName = WConfig::get('config.site_title');
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->IsHTML(true);
		$mail->AddAddress($to);
		$mail->Send();
		unset($mail);
	}

	/**
	 * Retrieves the User app's configuration stored in the users_config table.
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

	public function getRedirectURLWithParams($params) {
		// Find redirect URL
		$route = WRoute::route();
		$referer = WRoute::getReferer();
		$redirect_request = WRequest::get('redirect');

		if (!empty($params['redirect'])) {
			return $params['redirect'];
		} else if (!empty($redirect_request)) {
			return $redirect_request;
		} else if ($route['app'] != 'user') {
			// Login form loaded from an external application
			return WRoute::getDir().WRoute::getQuery();
		} else if (strpos($referer, 'user') === false) {
			return $referer;
		} else {
			return WRoute::getDir();
		}
	}
}

?>
