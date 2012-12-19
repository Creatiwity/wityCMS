<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Session handler
 * 
 * @auteur	Fofif
 * @version	$Id: WCore/WSession.php 0004 19-12-2012 Fofif $
 */

class WSession {
	/*
	 * SQL Table
	 */
	const USERS_TABLE = 'users';
	/*
	 * Minimum time betwen two POST requests
	 */
	const FLOOD_TIME = 15;
	/**
	 * Time before the session expires
	 */
	const TOKEN_EXPIRATION = 120;
	/*
	 * Inactivity time (minuts)
	 */
	//const ACTIVITY = 3;
	
	/**
	 * States
	 */
	const LOGIN_SUCCESS = 1;
	const LOGIN_MAX_ATTEMPT_REACHED = 2;
	
	/**
	 * Session setup
	 */
	public function __construct() {
		// No sid in HTML links
		ini_set('session.use_trans_sid', '0');
		session_name('wsid');
		
		// Start sessions
		session_start();
		
		if ($this->isLoaded()) {
			// Token expiration checking
			if (empty($_SESSION['token_expiration']) || time() >= $_SESSION['token_expiration']) {
				if (!$this->reloadSession($_SESSION['userid'], $_COOKIE['hash'])) {
					$this->closeSession();
				}
			}
		}
		// Attempt to load a user based on its cookies
		else if (isset($_COOKIE['userid']) && !empty($_COOKIE['hash'])) {
			// Hash => unique connection
			if (!$this->reloadSession(intval($_COOKIE['userid']), $_COOKIE['hash'])) {
				$this->closeSession();
			}
		}
	}
	
	/**
	 * @return bool Is the user connected?
	 */
	public static function isLoaded() {
		return isset($_SESSION['userid']);
	}
	
	/**
	 * Create a session for the user
	 * 
	 * @param string $nickname
	 * @param string $password
	 * @param int $remember Session life time (-1 = N/A)
	 */
	public function createSession($nickname, $password, $remember) {
		// Système de régulation en cas d'erreur multiple du couple pseudo/pass
		// On stocke dans la variable session $login_try le nombre de tentatives de connexion
		if (!isset($_SESSION['login_try']) || (isset($_SESSION['flood_time']) && $_SESSION['flood_time'] < time())) {
			$_SESSION['login_try'] = 0;
		} else if ($_SESSION['login_try'] >= self::MAX_LOGIN_ATTEMPT) {
			return self::LOGIN_MAX_ATTEMPT_REACHED;
		}
		
		// Treatment
		$nickname = trim($nickname);
		// Email to lower case
		if (strpos($nickname, '@') !== false) {
			$nickname = strtolower($nickname);
		}
		$password_hash = sha1($password);
		
		// Search a matching couple (nickname, password_hash) in DB
		$db = WSystem::getDB();
		$prep = $db->prepare('
			SELECT id, nickname, email, groupe, access
			FROM users
			WHERE (nickname = :nickname OR email = :nickname) AND password = :password
		');
		$prep->bindParam(':nickname', $nickname);
		$prep->bindParam(':password', $password_hash);
		$prep->execute();
		$data = $prep->fetch();
		
		// User found
		if (!empty($data)) {
			$this->setupSession($data['id'], $data);
			
			// Cookie setup
			if ($remember > 0) {
				$lifetime = time() + $remember;
				// Cookie setup
				setcookie('userid', $_SESSION['userid'], $lifetime, '/');
				setcookie('hash', $this->generate_hash($nickname, $password_hash), $lifetime, '/');
			}
			
			return self::LOGIN_SUCCESS; 
		} else {
			// Attempt + 1
			$_SESSION['login_try']++;
			return 0;
		}
	}
	
	/**
	 * Reload a user based on cookies
	 * 
	 * @param string $userid
	 * @param string $cookie_hash Connexion hash for checking
	 */
	private function reloadSession($userid, $cookie_hash) {
		$db = WSystem::getDB();
		$prep = $db->prepare('
			SELECT id, nickname, password, email, groupe, access
			FROM '.self::USERS_TABLE.'
			WHERE id = :userid
		');
		$prep->bindParam(':userid', $userid, PDO::PARAM_INT);
		$prep->execute();
		$data = $prep->fetch();
		
		if (!empty($data)) {
			// Check hash
			if ($cookie_hash == $this->generate_hash($data['nickname'], $data['password'])) {
				$this->setupSession($userid, $data);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Setup session variables for the user
	 * 
	 * @param string $userid
	 * @param array $data Data to store into $_SESSION
	 */
	public function setupSession($userid, $data) {
		$_SESSION['userid']   = $userid;
		$_SESSION['nickname'] = $data['nickname'];
		$_SESSION['email']    = $data['email'];
		$_SESSION['groupe']   = $data['groupe'];
		
		$_SESSION['accessString'] = $data['access'];
		$_SESSION['access'] = array();
		foreach (explode(',', $data['access']) as $access) {
			if (!empty($access)) {
				if (strpos($access, '|') !== false) {
					$split = explode('|', $access);
					$_SESSION['access'][$split[0]] = $split[1];
				} else {
					$_SESSION['access'][$access] = 0;
				}
			}
		}
		
		// Next checking time
		$_SESSION['token_expiration'] = time() + self::TOKEN_EXPIRATION;
	}
	
	/**
	 * Disconnect the user
	 */
	public function closeSession() {
		// Delete vars
		unset(
			$_SESSION['userid'], 
			$_SESSION['nickname'], 
			$_SESSION['email'], 
			$_SESSION['groupe'], 
			$_SESSION['accessString'], 
			$_SESSION['access'],
			$_SESSION['token_expiration']
		);
		
		// Reset cookies
		setcookie('userid', '', time()-3600, '/');
		setcookie('hash', '', time()-3600, '/');
	}
	
	/**
	 * Clean variables used to define a user loaded
	 */
	public function destroy() {
		$this->closeSession();
		
		$_SESSION = array();
		session_destroy();
		
		// Reset cookies
    	setcookie(session_name(), '', time()-3600, '/');
	}
	
	/**
	 * Generate a hash designed for the user computer, to be stored in a cookie
	 */
	public function generate_hash($nick, $pass, $environment = true) {
		$string = $nick.$pass;
		// Rajout de quelques valeurs rendant le hash lié à l'environnement de l'utilisateur
		if ($environment) {
			$string .= $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_ACCEPT_LANGUAGE']."*";
		}
		
		return sha1($string);
	}
	
	/**
	 * Système général d'antiflood
	 * Vérification du contenue de $_POST pour éviter le renvoie, volontaire ou non, redondant
	 * 
	 * @return bool flood dans la page
	 */
	public function check_flood() {
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$flood = true;
			
			// Vérification du référant
			if (empty($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
				header('location: '.WRoute::getDir());
				$flood = false;
			}
			// Vérification de la dernière requête
			else if (!empty($_SESSION['last_query']) && md5(serialize($_POST)) == $_SESSION['last_query']) {
				WNote::info("Modération", "Vous avez déjà envoyé ces informations.", 'assign');
				$flood = false;
			}
			// Vérification du temps de flood
			else if (empty($_SESSION['access'][0]) && !empty($_SESSION['flood_time']) && $_SESSION['flood_time'] > time()) {
				// Liste des exceptions échappant à cette vérification
				$exceptions = array('user');
				if (!in_array(WRoute::getApp(), $exceptions)) {
					WNote::info('Modération', 'Veuillez respecter le délai de '.self::FLOOD_TIME.' secondes entre deux postes.', 'assign');
					$flood = false;
				}
			}
			
			// Mise à jour des variables de flood
			$_SESSION['last_query'] = md5(serialize($_POST));
			
			// Mise à jour du temps de flood à l'extinction pour laisser les scripts moins prioritaires utiliser cette variable
			register_shutdown_function(array($this, 'upgrade_flood'), time() + self::FLOOD_TIME + 1);
			
			return $flood;
		} else {
			// Création de la variable session $flood_time
			if (!isset($_SESSION['flood_time'])) {
				$_SESSION['flood_time'] = 0;
			}
			
			// Remise à zero de la dernière requête
			$_SESSION['last_query'] = '';
		}
		
		return true;
	}
	
	/**
	 * Fonction de mise à jour du temps de flood
	 * 
	 * @param  int  timestamp limite
	 * @return void
	 */
	public function upgrade_flood($limit) {
		$_SESSION['flood_time'] = $limit;
	}
	
	/*
	// Gestion des sessions actives pour connaitre le nombre de connectés
	private function set_activity()
	{
		$sess_id = session_id();
		$now     = time();
		$limit   = $now + $this->online_time * 60;
		$sql     = Fc_SQL::instance();
		
		// Supprime les utilisateurs innactifs
		$sql->query('DELETE FROM '.fc_prefix.'online WHERE time < '.$now);
		
		// Status de l'utilisateur
		if (isset($_SESSION['access']))
			$status = (!empty($_SESSION['access'][0])) ? 2 : 1; // Admin / Membre
		else
			$status = 0; // Visiteur
		
		$req = $sql->query('
			SELECT id 
			FROM '.fc_prefix.'online 
			WHERE id = "'.$sess_id.'" OR ip = "'.$_SESSION['ip'].'"
		');
		if ($sql->num($req) > 0) // Vérifie s'il existe déjà dans la table
		{
			// Met à jour l'entrée (temps et status)
			$sql->query('
				UPDATE '.fc_prefix.'online 
				SET id = "'.$sess_id.'", time = '.$limit.', status = '.$status.', ip = "'.$_SESSION['ip'].'" 
				WHERE id = "'.$sess_id.'" OR ip = "'.$_SESSION['ip'].'"
			');
		}
		else
		{
			$sql->query('
				INSERT INTO '.fc_prefix.'online (id, time, status, ip) 
				VALUES ("'.$sess_id.'", '.$limit.', '.$status.', "'.$_SESSION['ip'].'")
			');
		}
	}
	
	// Renvoie un tableau contenant le nombre d'admins/membres/visiteurs actifs
	public function get_activity()
	{
		$sql = Fc_SQL::instance();
		
		if (!isset($this->online))
		{
			$this->online = array(0, 0, 0);
			$req = $sql->query('SELECT status, COUNT(*) FROM '.fc_prefix.'online GROUP BY status');
			while (list($status, $count) = $this->sql->fetch($req))
			{
				$this->online[$status] = $count;
			}
		}
		
		return $this->online;
	}
	*/
}

?>
