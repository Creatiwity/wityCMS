<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/newsletter/admin/main.php 0001 06-10-2011 Fofif $
 */

class NewsletterAdminController extends WController {
	/*
	 * Les opérations du module
	 */
	protected $actionList = array(
		'liste' => "Courriels envoyés",
		'write' => "Rédiger un courriel",
		'read' => "\Lecture d'une newsletter"
	);
	
	public function __construct() {
		// Chargement des modèles
		include 'model.php';
		$this->model = new NewsletterAdminModel();
		
		include 'view.php';
		$this->setView(new NewsletterAdminView($this->model));
	}
	
	public function launch() {
		// Les notes
		WNote::treatNoteSession();
		
		$action = $this->getAskedAction();
		$this->forward($action, 'liste');
	}
	
	/**
	 * Récupération de l'id de l'utilisateur fourni en Url
	 * @param void
	 * @return int
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[1])) {
			return -1;
		} else {
			list ($id) = explode('-', $args[1]);
			return intval($id);
		}
	}
	
	/**
	 * Listage des newsletters
	 */
	protected function liste() {
		// Traitement du tri
		$args = WRoute::getArgs();
		$sortData = explode('-', @$args[0]);
		$sortBy = empty($sortData) ? '' : array_shift($sortData);
		$sens = empty($sortData) ? '' : array_shift($sortData);
		$page = empty($sortData) ? 1 : $sortData[0];
		
		$this->view->liste($sortBy, $sens, $page);
		$this->render('liste');
	}
	
	protected function read() {
		$this->view->read($this->getId());
		$this->render('read');
	}
	
	protected function write() {
		$newsletter = WRequest::get(array('destinataires', 'more', 'de', 'objet', 'message'), null, 'POST', false);
		// Le formulaire a-t-il été envoyé ?
		if (!in_array(null, $newsletter, true)) {
			$erreurs = array();
			
			/*
			 * VARIABLES NEWSLETTER
			 */
			if (!empty($newsletter['de']) && !preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $newsletter['de'])) {
				$erreurs[] = "L'email de l'expéditeur est invalide.";
			}
			if (empty($newsletter['destinataires'])) {
				$erreurs[] = "Il manque des destinataires.";
			}
			if (empty($newsletter['objet'])) {
				$erreurs[] = "Veuillez préciser un objet.";
			}
			if (empty($newsletter['message'])) {
				$erreurs[] = "Le message que vous tentez d'envoyer est vide.";
			}
			
			// En cas d'erreur
			if (!empty($erreurs)) {
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
				$this->view->write($newsletter);
				$this->render('write');
			} else {
				// ====> Envoi des emails <====
				include LIBS_DIR.'phpmailer'.DS.'class.phpmailer.php';
				$mail = new PHPMailer();
				$mail->CharSet = 'utf-8';
				$mail->IsHTML(true);
				$mail->From = $newsletter['de'];
				$mail->FromName = 'Forum Est-Horizon';
				$mail->Subject = $newsletter['objet'];
				$mail->Body = $newsletter['message'];
				// $mail->AddCC = $newsletter['cc'];
				
				// Upload du fichier
				$newsletter['attachment'] = ''; // init
				if (!empty($_FILES['attachment']['name'])) {
					include HELPERS_DIR.'upload/upload.php';
					$upload = new Upload($_FILES['attachment']);
					$upload->file_overwrite = true;
					$upload->Process(WT_PATH.'upload/newsletters/');
					
					// Attachment à l'email si succès de l'upload
					if ($upload->processed) {
						$file_path = WT_PATH.'upload/newsletters/'.$upload->file_dst_name;
						$mail->AddAttachment($file_path);
						$newsletter['attachment'] = $file_path;
					}
				}
				
				//$mails = $this->model->getMailsArray($newsletter['destinataires']);
				$mails_string = "";
				foreach ($newsletter['destinataires'] as $m => $v) {
					$m = trim($m);
					if (!empty($m)) {
						$mails_string .= $m."; ";
						$mail->ClearAddresses();
						$mail->AddAddress($m);
						$mail->Send();
					}
				}
				
				// Envoi à "more"
				foreach (explode(',', $newsletter['more']) as $m) {
					$m = trim($m);
					if (!empty($m)) {
						$mails_string .= $m."; ";
						$mail->ClearAddresses();
						$mail->AddAddress($m);
						$mail->Send();
					}
				}
				
				// Envoi d'une copie à l'adresse du forum
				$mail->ClearAddresses();
				$mail->AddAddress('forum@mines.inpl-nancy.fr');
				$mail->Send();
				
				// Données pour la bdd
				$newsletter['destinataires'] = $mails_string;
				
				// ====> Sauvegarde de l'email en bdd <====
				if (!$this->model->saveNewsletter($newsletter)) {
					WNote::error("Erreur sauvegarde courriel", "Une erreur inconnue s'est produite.", 'session');
				}
				
				WNote::success("Courriel envoyé", "La newsletter a été envoyée avec succès.", 'session');
				// Redirection
				header('location: '.WRoute::getDir().'/admin/newsletter/');
			}
		} else {
			$this->view->write();
			$this->render('write');
		}
	}
	
	protected function getMails() {
		$data = $this->model->getMailsArray(WRequest::get('type'));
		
		// Formattage
		$string = '';
		$count = 0;
		foreach ($data as $mail) {
			if (!empty($mail['email'])) {
				$string .= "<li><label><input type=\"checkbox\" name=\"destinataires[".$mail['email']."]\" checked=\"checked\" /> ".$mail['email']."</label></li>\n";
				$count++;
			}
		}
		$string = "<div class=\"listWrapper\"><ul>\n"
			.$string
			."</ul></div>\n"
			."<div class=\"count\"><span id=\"toCount\">".$count."</span> contacts</div>";
		
		echo $string;
	}
}

?>
