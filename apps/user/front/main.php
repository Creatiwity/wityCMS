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
		
		$this->session = WSystem::getSession();
	}
	
	public function launch() {
		switch ($this->getAskedAction()) {
			case 'login':
			case 'connexion':
			default:
				$this->forward('login');
				break;
			
			case 'logout':
			case 'deconnexion':
				$this->forward('logout');
				break;
			
			case 'register':
				$this->forward('register');
				break;
			
			case 'confirm':
				$this->forward('confirm');
				break;
		}
	}
	
	/**
	 * Connexion d'un membre
	 */
	protected function login() {
		if ($this->session->isConnected()) {
			WNote::error('user_connected', "Inutile d'accéder à cette page si vous êtes connecté(e).", 'display');
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
					$this->model->updateLastActivity($_SESSION['userid']);
					
					// Redirect
					WNote::success('login_success', 'Connexion réussie');
					break;
				
				case WSession::LOGIN_MAX_ATTEMPT_REACHED:
					WNote::error('login_max_attempt', "Vous avez atteint le nombre maximum de tentatives de connexion autorisées.\nMerci d'attendre un instant avant de réessayer.");
					break;
				
				case 0:
					WNote::error('login_error', "Le couple <em>nom d'utilisateur / mot de passe</em> est erroné.");
					break;
				
				default:
					break;
			}
		}
		header('location: '.$data['redirect']);
		/*
		$this->view->connexion($data['redirect']);
		$this->view->render();
		*/
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
		WNote::success('user_disconnected', "Vous êtes maintenant déconnecté.");
		header('location: '.WRoute::getDir());
	}
	
	protected function register() {
		$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'firstname', 'lastname', 'adresse', 'code_postal', 'ville', 'email'));
		// Le formulaire a-t-il été envoyé ?
		if (!in_array(null, $data, true)) {
			// Liste des erreurs
			$erreur = array();
			
			// Vérifie la disponibilité du pseudo
			if (empty($data['nickname'])) {
				$erreur[] = "Pseudonyme manquant.";
			} else if (!$this->model->nicknameAvailable($data['nickname'])) {
				$erreur[] = "Ce pseudonyme est déjà pris.";
			}
			
			// Passwords identiques
			if (!empty($data['password'])) {
				if ($data['password'] === $data['password_conf']) {
					$password_save = $data['password'];
					$data['password'] = sha1($data['password']);
				} else {
					$erreur[] = "Les mots de passe sont différents.";
				}
			} else {
				$erreur[] = "Aucun mot de passe n'a été fourni.";
			}
			
			// Email
			if (!empty($data['email']) && !preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $data['email'])) {
				$erreurs[] = "L'email fourni est invalide.";
			} else if (!$this->model->emailAvailable($data['email'])) {
				$erreur[] = "Cet email est déjà pris.";
			}
			
			// Création du hash de confirmation
			$data['confirm'] = uniqid();
			$data['groupe'] = 0;
			
			// Affichage des éventuelles erreurs
			if (!empty($erreur)) {
				WNote::error('data_errors', implode("<br />\n", $erreur));
				header('location: '.WRoute::getDir());
			} else {
				// Création de l'utilisateur
				if ($this->model->createUser($data)) {
					// Envoi des infos par email
					$mail = WHelper::load('phpmailer');
					$mail->CharSet = 'utf-8';
					$mail->From = "contact@winafile.com";
					$mail->FromName = "Winafile";
					$mail->Subject = "Création de votre compte sur Winafile.com";
					$mail->Body = "Bonjour,<br /><br />
Vous venez de vous inscrire sur le site Winafile.com.<br /><br />

Veuillez trouver ci-dessous vos données de connexion :<br />
Identifiant : ".$data['nickname']."<br />
Password : ".$password_save."<br /><br />

Pour finaliser votre demande, veuillez cliquer sur le lien ci-dessous :<br /><br />
<a href=\"http://www.winafile.com/user/confirm/".$data['confirm']."\">Valider la demande</a><br /><br />

Si ce lien ne fonctionne pas, veuillez copier l'adresse suivante dans votre navigateur :<br />
http://www.winafile.com/user/confirm/".$data['confirm']."<br /><br />

Profitez de l'upload gagnante !<br /><br />

<strong>L'équipe Winafile</strong>";
					$mail->IsHTML(true);
					$mail->AddAddress($data['email']);
					$mail->Send();
					unset($mail);
					
					WNote::success('user_registered', "Votre compte a été créé avec succès.<br /><br />Vous venez de recevoir un email à l'adresse que vous nous avez indiquée pour valider votre compte.", 'display');
				} else {
					WNote::error('user_registration_failure', "Une erreur inconnue s'est produite lors de la création de votre compte.");
					header('location: '.WRoute::getDir());
				}
			}
		} else {
			header('location: '.WRoute::getDir());
		}
	}
	
	protected function confirm() {
		list(, $confirm) = WRoute::getArgs();
		if (!empty($confirm)) {
			$this->model->validateAccount($confirm);
			WNote::success('user_validated', "Votre compte sur Winafile.com vient d'être validé.
			<br /><br />
			Vous pouvez maintenant vous connecter à votre compte et commencer à uploader des fichiers.", 'display');
		} else {
			header('location: '.WRoute::getDir());
		}
	}
}

?>
