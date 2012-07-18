<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/user/front/model.php 0004 17-09-2011 Fofif $
 */

class UserModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	public function validId($id) {
		$prep = $this->db->prepare('
			SELECT * FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Vérifie qu'un pseudo est disponible
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
	 * Vérifie qu'une adresse email n'est pas déjà présente dans la base
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
	 * Obtenir le dernier id inséré dans la table
	 * Peut s'avérer utile pour d'autres apps
	 */
	public function getLastUserId() {
		$prep = $this->db->prepare('
			SELECT id FROM users ORDER BY id DESC LIMIT 1
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	public function countUsers() {
		$prep = $this->db->prepare('
			SELECT COUNT(*) FROM users
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	public function countUsersWithFilters(array $filtres = array()) {
		$allowedFiltres = array('nickname', 'email', 'firstname', 'lastname');
		$filtreString = "";
		foreach ($filtres as $fname => $fvalue) {
			if (!empty($fvalue) && in_array($fname, $allowedFiltres)) {
				$filtreString .= $fname." LIKE '%".$fvalue."%' AND ";
			}
		}
		if (!empty($filtres['groupe'])) {
			$filtreString .= "groupe = ".intval($filtres['groupe'])." AND ";
		}
		$filtreString = substr($filtreString, 0, -5);
		
		$prep = $this->db->prepare("
			SELECT COUNT(*)
			FROM users
			LEFT JOIN users_cats
			ON groupe = users_cats.id
			".(!empty($filtreString) ? "WHERE ".$filtreString : "")."
		");
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	public function getUserList($from, $number, $order = 'nickname', $asc = true, array $filtres = array()) {
		$allowedFiltres = array('nickname', 'email', 'firstname', 'lastname');
		$filtreString = "";
		foreach ($filtres as $fname => $fvalue) {
			if (!empty($fvalue) && in_array($fname, $allowedFiltres)) {
				$filtreString .= $fname." LIKE '%".$fvalue."%' AND ";
			}
		}
		if (!empty($filtres['groupe'])) {
			$filtreString .= "groupe = ".intval($filtres['groupe'])." AND ";
		}
		$filtreString = substr($filtreString, 0, -5);
		
		$prep = $this->db->prepare("
			SELECT users.id, nickname, email, name AS groupe, users.access, DATE_FORMAT(date, '%d/%m/%Y %H:%i') AS fdate, DATE_FORMAT(last_activity, '%d/%m/%Y %H:%i') AS factivity
			FROM users
			LEFT JOIN users_cats
			ON groupe = users_cats.id
			".(!empty($filtreString) ? "WHERE ".$filtreString : "")."
			ORDER BY ".$order." ".($asc ? 'ASC' : 'DESC')."
			LIMIT :start, :number
		");
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetchAll();
	}
	
	public function getUserData($userid) {
		$prep = $this->db->prepare('
			SELECT nickname, email, groupe, access
			FROM users
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $userid, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetch();
	}
	
	public function matchUser($nickname, $password) {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, groupe, access
			FROM users
			WHERE (nickname = :nickname OR email = :nickname) AND password = :password
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->bindParam(':password', $password);
		$prep->execute();
		return $prep->fetch();
	}
	
	public function createUser($data) {
		$prep = $this->db->prepare('
			INSERT INTO users(nickname, password, confirm, email, firstname, lastname, groupe, access, ip)
			VALUES (:nick, :pass, :confirm, :email, :firstname, :lastname, :groupe, :access, :ip)
		');
		$prep->bindParam(':nick', $data['nickname']);
		$prep->bindParam(':pass', $data['pass']);
		$confirm = isset($data['confirm']) ? $data['confirm'] : '';
		$prep->bindParam(':confirm', $confirm);
		$prep->bindParam(':email', $data['email']);
		$firstname = isset($data['firstname']) ? $data['firstname'] : '';
		$prep->bindParam(':firstname', $firstname);
		$lastname = isset($data['lastname']) ? $data['lastname'] : '';
		$prep->bindParam(':lastname', $lastname);
		$access = isset($data['access']) ? $data['access'] : '';
		$prep->bindParam(':access', $access);
		$prep->bindParam(':groupe', $data['groupe']);
		$prep->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		return $prep->execute();
	}
	
	public function updateUser($id, $data) {
		$string = '';
		foreach ($data as $key => $value) {
			$string .= $key.' = '.$this->db->quote($value).', ';
		}
		$string = substr($string, 0, -2);
		
		return $this->db->query('
			UPDATE users
			SET '.$string.'
			WHERE id = '.$id
		);
	}
	
	/**
	 * Met à jour la date de dernière activité d'un utilisateur
	 */
	public function updateLastActivity($userid) {
		$prep = $this->db->prepare('
			UPDATE users
			SET last_activity = NOW()
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $userid, PDO::PARAM_INT);
		return $prep->execute();
	}
}

?>
