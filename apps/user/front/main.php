<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/user/front/main.php 0005 24-04-2012 Fofif $
 */

class UserController extends WController {
	/*
	 * Durée par défaut d'une session
	 */
	const REMEMBER_TIME = 604800; // 1 semaine
	/*
	 * Nombre maximum de tentatives de connexion
	 */
	const MAX_LOGIN_ATTEMPT = 3;
	/*
	 * Pointeurs vers WSession et UserModel
	 */
	private $session, $model;
	/**
	 * Les constantes d'erreur
	 */
	const ANTIFLOOD_ERROR = 2;
	
	public function __construct() {
		include_once 'model.php';
		$this->model = new UserModel();
		
		include 'view.php';
		$this->view = new UserView();
	}
	
	public function launch() {
		$this->session = WSystem::getSession();
		
		$action = $this->getAskedAction();
		$this->forward($action, 'connexion');
	}
	
	/**
	 * Fonction de connexion d'un utilisateur
	 * 
	 * @param string $nick
	 * @param string $pass
	 * @param int $remember temps de connexion (-1 = non spécifié)
	 * @param mixed  $remember durée de la session si précisée
	 */
	public function login($nick, $pass, $remember) {
		// Système de régulation en cas d'erreur multiple du couple pseudo/pass
		// On stocke dans la variable session $login_try le nombre de tentatives de connexion
		if (!isset($_SESSION['login_try']) || (isset($_SESSION['flood_time']) && $_SESSION['flood_time'] < time())) {
			$_SESSION['login_try'] = 0;
		} else if ($_SESSION['login_try'] >= self::MAX_LOGIN_ATTEMPT) {
			// erreur type antiflood
			return self::ANTIFLOOD_ERROR;
		}
		
		// Petit traitement des informations
		$nick = trim($nick);
		if (strpos($nick, '@') !== false) {
			$nick = strtolower($nick);
		}
		$pass = sha1($pass);
		
		// Recherche d'une correspondance dans la bdd pour le couple (user, password)
		$data = $this->model->matchUser($nick, $pass);
		if (!empty($data)) {
			$this->session->loadUser($data['id'], $data);
			$this->model->updateLastActivity($data['id']);
			
			// Enregistrement du cookie si demandé
			if ($remember > 0) {
				$lifetime = time() + $remember;
				// see WSession
				setcookie('userid', $_SESSION['userid'], $lifetime, '/');
				setcookie('hash', $this->session->generate_hash($nick, $pass), $lifetime, '/');
			}
			
			return 1;
		} else {
			// Incrémente le nombre d'essais
			$_SESSION['login_try']++;
			return 0;
		}
	}
	
	/**
	 * Connexion d'un membre
	 */
	protected function connexion() {
		if ($this->session->isLoaded()) {
			WNote::error("Accès interdit", "Inutile d'accéder à cette page si vous êtes connecté(e).", 'display');
		} else {
			$data = WRequest::get(array('nick', 'pass', 'remember', 'time', 'redirect'), null, 'POST');
			
			// Affichage du formulaire de connexion
			if (empty($data['redirect'])) {
				if (WRoute::getApp() != 'user') {
					$data['redirect'] = WRoute::getURL();
				} else {
					$referer = WRoute::getReferer();
					// On évite de rediriger vers une page du module user
					$data['redirect'] = (strpos($referer, 'user') === false) ? $referer : WRoute::getBase();
				}
			}
			
			if (!empty($data['nick']) && !empty($data['pass'])) {
				// L'utilisateur demande-t-il une connexion automatique ? (de combien de temps ?)
				$rememberTime = (!is_null($data['remember'])) ? self::REMEMBER_TIME : intval($data['time']) * 60;
				
				// Connexion
				switch ($this->login($data['nick'], $data['pass'], $rememberTime)) {
					case 1: // Connexion réussie
						header('location: '.$data['redirect']);
						return;
						break;
					case self::ANTIFLOOD_ERROR:
						WNote::error("Erreur de connexion", "Vous avez atteint le nombre maximum de tentatives de connexion autorisées.<br />
Merci d'attendre un instant avant de réessayer.", 'assign');
						break;
					default:
						WNote::error("Erreur de connexion", "Le couple <em>nom d'utilisateur / mot de passe</em> est erroné.", 'assign');
						break;
				}
			}
			
			$this->view->connexion($data['redirect']);
			$this->render('connexion');
		}
	}
	
	/**
	 * Déconnexion
	 */
	protected function deconnexion() {
		if (!$this->session->isLoaded()) {
			WNote::error("Accès interdit !", "Vous devez être connecté(e) pour accéder à cette page.", 'display');
		} else {
			// Destruction de la session
			$this->session->logout();
			
			// Redirection
			WNote::success("Déconnexion", "Vous êtes maintenant déconnecté.", 'display');
		}
	}
}

?>
