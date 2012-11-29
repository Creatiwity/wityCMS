<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Gestion de sessions
 * 
 * @auteur	Fofif
 * @version	$Id: WCore/WSession.php 0003 02-08-2011 Fofif $
 */

class WSession {
	// Table des utilisateurs
	const USER_TABLE = 'users';
	// Temps avant d'être considéré comme inactif en minutes
	const ACTIVITY = 3;
	// Temps minimum séparant l'envoie de deux postes en secondes
	const FLOOD_TIME = 30;
	
	/**
	 * Setup des sessions
	 */
	public function __construct() {
		// Pas de sid dans les liens
		ini_set('session.use_trans_sid', '0');
		
		// Nom de session
		session_name('wsid');
		
		// Démarrage des sessions
		session_start();
		
		// Tentative de chargement de l'utilisateur en vérifiant les cookies
		if (!$this->isLoaded() && isset($_COOKIE['userid']) && !empty($_COOKIE['hash'])) {
			// Le hash assure une connexion unique
			$this->reloadSession(intval($_COOKIE['userid']), $_COOKIE['hash']);
		}
	}
	
	/**
	 * Fonction de déconnexion de l'utilisateur
	 */
	public function logout() {
		// Suppression des cookies
		setcookie('userid', '', time()-3600, '/');
		setcookie('hash', '', time()-3600, '/');
		
		// Suppression totale de la session
		$this->destroy();
		
		return true;
	}
	
	/**
	 * Chargement d'un utilisateur
	 * 
	 * @param string $userid id de l'utilisateur
	 * @param string $cookie_hash hash de connexion pour la vérification
	 */
	private function reloadSession($userid, $cookie_hash) {
		$db = WSystem::getDB();
		
		$query = $db->query('SELECT id, nickname, password, email, groupe, access FROM '.self::USER_TABLE.' WHERE id = '.$userid);
		if ($query->rowCount() > 0) {
			// Chargement des informations
			$data = $query->fetch(PDO::FETCH_ASSOC);
			
			// Vérification du hash
			if ($cookie_hash == $this->generate_hash($data['nickname'], $data['password'])) {
				$this->loadUser($userid, $data);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Chargement en session des données d'un utilisateur
	 * 
	 * @param string $userid id de l'utilisateur
	 */
	public function loadUser($userid, $data) {
		// Création des variables de session
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
	}
	
	/**
	 * @return bool l'utilisateur est-il connecté ?
	 */
	public static function isLoaded() {
		return isset($_SESSION['userid']);
	}
	
	// Génère un hash caractéristique de l'utilisateur
	public function generate_hash($nick, $pass, $environment = true) {
		$string = $nick.$pass;
		// Rajout de quelques valeurs rendant le hash lié à l'environnement de l'utilisateur
		if ($environment) {
			$string .= $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_ACCEPT_LANGUAGE']."*";
		}
		
		return sha1($string);
	}
	
	/**
	 * Destruction de la session php
	 */
	public function destroy() {
		// Suppression des variables de session
		$_SESSION = array();
		
		// Destruction de la session
		session_destroy();
		
		// Elimination du cookie s'il existe
		if (isset($_COOKIE[session_name()])) {
    		setcookie(session_name(), '', time()-3600, '/');
		}
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
