<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif <Johan Dufau>
 * @version	$Id: apps/contact/front/main.php 0001 19-05-2011 Julien1619 $
 */

class ContactController extends WController {
	/*
	 * Chargement du modèle et de la view
	 */
	public function __construct() {
		include 'model.php';
		$this->model = new ContactModel();
		
		include 'view.php';
		$this->setView(new ContactView($this->model));
	}
	
	public function launch() {
		// Les notes
		WNote::treatNoteSession();
		
		$action = $this->getAskedAction();
		$this->forward($action, 'contact');
	}
	
	public function contact() {
		$data = WRequest::get(array( 'name', 'organisme', 'email', 'objet', 'message'), null, 'POST', false);
		if (!in_array(null, $data, true)) {
			require_once LIBS_DIR.'recaptcha-php-1.11/recaptchalib.php';
			$privatekey = "6LdLDMoSAAAAAJ-zpQamgpy4rAoJtjIxz24P6rEG";
			$resp = recaptcha_check_answer($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);
			
			if (!$resp->is_valid) {
				WNote::error("Captcha invalide", "Le code de vérification que vous avez entré est incorrect. Merci de réessayer.", 'assign');
				$this->view->contact($data);
				$this->render('contact');
			} else {
				$erreurs = array();
				
				// Rajout du userid
				if(isset($_SESSION['userid'])) {
					$data['userid'] = $_SESSION['userid'];
				} else {
					$data['userid'] = '';
				}
				
				if (empty($data['email']) || !preg_match('#^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$#i', $data['email'])) {
					$erreurs[] = "Vous devez fournir une adresse email valide.";
				}
				
				// En cas d'erreur
				if (!empty($erreurs)) {
					WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'display');
				} else {
					// Création du mail de contact
					$mailData = array(
						'userid' => $data['userid'],
						'name' => $data['name'],
						'organisme' => $data['organisme'],
						'email' => $data['email'],
						'objet' => $data['objet'],
						'message' => $data['message'],
					);
					if (!$this->model->createMail($mailData)) {
						WNote::error("Erreur création mail", "Votre mail n'a pas été envoyé.<br />Veuillez refaire un essai.", 'display');
					} else {
						WNote::success("Mail envoyé", "Votre mail a été envoyé avec succès.<br /><br />
							Vous venez de recevoir une copie à l'adresse email que vous avez indiquée.", 'display');
						
						// Envoi des emails
						include LIBS_DIR.'phpmailer'.DS.'class.phpmailer.php';
						
						//Envoi du mail au forum
						$mail = new PHPMailer();
						$mail->CharSet = 'utf-8';
						$mail->From = $data['email'];
						$mail->FromName = $data['name'].' de '.$data['organisme'];
						$mail->Subject = $data['objet'];
						$mail->Body = nl2br($data['message']);
						$mail->IsHTML(true);
						$mail->AddAddress('forum@mines.inpl-nancy.fr');
						$mail->Send();
						
						unset($mail);
						
						//Envoi de la copie à l'expéditeur
						
						$mail = new PHPMailer();
						$mail->CharSet = 'utf-8';
						$mail->From = 'contact@est-horizon.com';
						$mail->FromName = 'Forum Est-Horizon 2011';
						$mail->Subject = 'Votre demande de contact pour le Forum Est-Horizon 2011';
						$mail->Body = "<h1>Votre demande de contact pour le Forum Est-Horizon 2011</h1>
						<p>Veuillez trouver ci-dessous la copie de votre demande de contact</p>
						<div style=\"background:#f2f39c;margin-left:20px;\">
						<h2>Expéditeur : ".$data['name']." de ".$data['organisme']."</h2>
						<h2>Objet : ".$data['objet']."</h2>
						".nl2br($data['message'])."<br/></div>
						<h2>Le Forum Est-Horizon 2011</h2>";
						$mail->IsHTML(true);
						$mail->AddAddress($data['email']);
						$mail->Send();
					}
				}
			}
		} else {
			$this->view->contact();
			$this->render('contact');
		}
	}
}

?>