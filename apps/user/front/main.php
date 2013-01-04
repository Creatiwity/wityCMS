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
	 * Instance of WSession
	 */
	private $session;
	/**
	 * Instance of UserModel
	 */
	private $model;
	
	public function __construct() {
		include_once 'model.php';
		$this->model = new UserModel();
		
		include 'view.php';
		$this->view = new UserView();
	}
	
	public function launch() {
		$this->session = WSystem::getSession();
		
		switch ($this->getAskedAction()) {
			case 'login':
			case 'connexion':
				$action = 'login';
				break;
			
			case 'logout':
			case 'deconnexion':
				$action = 'logout';
				break;
			
			default:
				$action = 'login';
				break;
		}
		$this->forward($action);
	}
	
	/**
	 * Connexion d'un membre
	 */
	protected function login() {
		if ($this->session->isConnected()) {
			WNote::error("user_connected", "Inutile d'accéder à cette page si vous êtes connecté(e).", 'display');
			return;
		}
		
		// Get data
		$data = WRequest::getAssoc(array('nickname', 'password', 'remember', 'time', 'redirect'), null, 'POST');
		
		// Find redirect URL
		if (empty($data['redirect'])) {
			if (WRoute::getApp() != 'user') {
				$data['redirect'] = WRoute::getURL();
			} else {
				$referer = WRoute::getReferer();
				// On évite de rediriger vers une page du module user
				$data['redirect'] = (strpos($referer, 'user') === false) ? $referer : WRoute::getBase();
			}
		}
		
		if (!empty($data['nickname']) && !empty($data['password'])) {
			// L'utilisateur demande-t-il une connexion automatique ? (de combien de temps ?)
			$rememberTime = (!is_null($data['remember'])) ? self::REMEMBER_TIME : intval($data['time']) * 60;
			
			// Connexion
			switch ($this->session->createSession($data['nickname'], $data['password'], $rememberTime)) {
				case WSession::LOGIN_SUCCESS:
					// Clean
					unset($_SESSION['login_try']);
					
					// Update activity
					$this->model->updateLastActivity($data['id']);
					
					// Redirect
					header('location: '.$data['redirect']);
					return;
				
				case WSession::LOGIN_MAX_ATTEMPT_REACHED:
					WNote::error("login_max_attempt", "Vous avez atteint le nombre maximum de tentatives de connexion autorisées.\nMerci d'attendre un instant avant de réessayer.", 'assign');
					break;
				
				default:
					WNote::error("login_error", "Le couple <em>nom d'utilisateur / mot de passe</em> est erroné.", 'assign');
					break;
			}
		}
		
		$this->view->connexion($data['redirect']);
		$this->render('connexion');
	}
	
	/**
	 * Log out the user
	 */
	protected function logout() {
		if ($this->session->isConnected()) {
			// Destroy the session of the user
			$this->session->closeSession();
		}
		
		// Redirection
		WNote::success("user_disconnected", "Vous êtes maintenant déconnecté.", 'display');
	}
}

?>
