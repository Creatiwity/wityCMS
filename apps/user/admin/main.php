<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: apps/user/admin/main.php 0003 02-02-2012 Fofif $
 */

class UserAdminController extends WController {
	public function __construct() {
		// Chargement des modèles
		include 'model.php';
		$this->model = new UserAdminModel();
		
		include 'view.php';
		$this->setView(new UserAdminView($this->model));
	}
	
	/**
	 * Récupération de l'id de l'utilisateur fourni en Url
	 * @param void
	 * @return int
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (!isset($args[1])) {
			return -1;
		} else {
			list ($id) = explode('-', $args[1]);
			return intval($id);
		}
	}
	
	protected function liste() {
		// Options de tri
		$args = WRoute::getArgs();
		$sortData = explode('-', array_shift($args));
		$sortBy = empty($sortData) ? '' : array_shift($sortData);
		$sens = empty($sortData) ? '' : array_shift($sortData);
		$page = empty($sortData) ? 1 : $sortData[0];
		
		// Filtres
		$filtres = WRequest::getAssoc(array('nickname', 'email', 'firstname', 'lastname', 'groupe'));
		
		$this->view->liste($sortBy, $sens, $page, $filtres);
		$this->view->render('liste');
	}
	
	protected function add() {
		$data = WRequest::getAssoc(array('nickname', 'pass', 'pass_confirm', 'email', 'groupe'));
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
			if (!empty($data['pass'])) {
				if ($data['pass'] === $data['pass_confirm']) {
					$data['pass'] = sha1($data['pass']);
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
			
			// Niveaux admin
			list($type, $access, $level) = WRequest::get(array('type', 'access', 'level'));
			if ($type == 'all') {
				$data['access'] = 'all';
			} else if ($type == 'perso') {
				$a = array();
				foreach ($access as $key => $v) {
					if (isset($level[$key])) {
						$a[] = $key.'|'.intval($level[$key]);
					} else {
						$a[] = $key.'|0';
					}
				}
				$data['access'] = implode(',', $a);
			} else {
				$data['access'] = '';
			}
			
			// Affichage des éventuelles erreurs
			if (!empty($erreur)) {
				WNote::error("Informations invalides", implode("<br />\n", $erreur), 'assign');
				$this->view->add($data);
				$this->render('add');
			} else {
				// Création de l'utilisateur
				if ($this->model->createUser($data)) {
					// Envoi des infos par email
					if (!empty($data['email']) && WRequest::get('emailwarning') == 'on') {
						include LIBS_DIR.'phpmailer'.DS.'class.phpmailer.php';
						$mail = new PHPMailer();
						$mail->CharSet = 'utf-8';
						$mail->From = "forum@mines.inpl-nancy.fr";
						$mail->FromName = "Forum Est-Horizon";
						$mail->Subject = "Données de connexion au site Forum Est-Horizon";
						$mail->Body = "Bonjour,<br /><br />
Un compte utilisateur vient de vous être créé sur le site www.est-horizon.com.<br /><br />
Pour vous connecter, rendez-vous à l'adresse <a href=\"http://www.est-horizon.com/admin/\">www.est-horizon.com/admin/</a>.<br /><br />
Voici vos données de connexion :<br />
<strong>Identifiant :</strong> ".$data['nickname']."<br />
<strong>Mot de passe :</strong> ".$data['pass_confirm']."<br /><br />
Ces informations sont personnelles.<br />
Pour tout changement, merci de contacter le pôle informatique.<br /><br />
L'administrateur du site du Forum Est-Horizon<br /><br />
--------------<br />
Ceci est un message automatique.";
						$mail->IsHTML(true);
						$mail->AddAddress($data['email']);
						$mail->Send();
						unset($mail);
					}
					WNote::success("Ajout d'utilisateur", "L'utilisateur <strong>".$data['nickname']."</strong> a été ajouté avec succès.", 'session');
					header('location: '.WRoute::getDir().'admin/user/');
				} else {
					WNote::error("Erreur lors de l'ajout", "Une erreur inconnue s'est produite.", 'assign');
					$this->view->add();
					$this->render('add');
				}
			}
		} else {
			$this->view->add();
			$this->view->render('add');
		}
	}
	
	protected function edit() {
		$userid = $this->getId();
		
		$data = WRequest::getAssoc(array('nickname', 'email', 'groupe'));
		// Le formulaire a-t-il été envoyé ?
		if (!in_array(null, $data, true)) {
			$data = array_merge($data, WRequest::getAssoc(array('password', 'password_confirm', 'access')));
			$erreurs = array();
			
			// Chargement des données actuelles
			$dbData = $this->model->getUserData($userid);
			
			// Vérification du pseudo
			if (empty($data['nickname'])) {
				$erreurs[] = "Pseudonyme manquant.";
			} else if ($data['nickname'] != $dbData['nickname'] && !$this->model->nicknameAvailable($data['nickname'])) {
				// On a vérifié que le pseudo a été changé
				$erreurs[] = "Ce pseudonyme est déjà réservé.";
			}
			
			// Passwords identiques
			if (!empty($data['password'])) {
				if ($data['password'] === $data['password_confirm']) {
					// Hashage
					$data['password'] = sha1($data['password']);
					unset($data['password_confirm']);
				} else {
					$erreurs[] = "Les mots de passe sont différents.";
				}
			} else {
				unset($data['password']);
			}
			unset($data['password_confirm']);
			
			// Email
			if (!empty($data['email']) && !preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $data['email'])) {
				$erreurs[] = "L'email fourni est invalide.";
			} else if ($data['email'] != $dbData['email'] && !$this->model->emailAvailable($data['email'])) {
				$erreurs[] = "Cet email est déjà pris.";
			}
			
			// Niveaux admin
			list($type, $access, $level) = WRequest::get(array('type', 'access', 'level'));
			if ($type == 'all') {
				$data['access'] = 'all';
			} else if ($type == 'perso') {
				$a = array();
				if (is_array($access)) {
					foreach ($access as $key => $v) {
						if (isset($level[$key])) {
							$a[] = $key.'|'.intval($level[$key]);
						} else {
							$a[] = $key.'|0';
						}
					}
				}
				$data['access'] = implode(',', $a);
			} else {
				$data['access'] = '';
			}
			
			// En cas d'erreur
			if (!empty($erreurs)) {
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
				$this->view->edit($userid);
				$this->view->render('edit');
			} else {
				// Mise à jour des infos
				if ($this->model->updateUser($userid, $data)) {
					WNote::success("Edition d'utilisateur", "L'utilisateur <strong>".$data['nickname']."</strong> a été mis à jour avec succès.", 'session');
					header('location: '.WRoute::getDir().'admin/user/edit/'.$userid);
				} else {
					WNote::error("Erreur lors de l'édition", "Une erreur inconnue s'est produite.", 'assign');
					$this->view->edit($userid);
					$this->view->render('add');
				}
			}
		} else {
			if (!$this->model->validId($userid)) {
				WNote::error("Utilisateur inexistant", "L'utilisateur recherché n'existe pas ou plus.", 'assign');
				$this->liste();
			} else {
				$this->view->edit($userid);
				$this->view->render('edit');
			}
		}
	}
	
	protected function del() {
		$userid = $this->getId();
		if ($this->model->validId($userid)) {
			if (WRequest::get('confirm', null, 'POST') === '1') {
				$this->model->deleteUser($userid);
				WNote::success("Suppression d'utilisateur", "L'utilisateur a été supprimé avec succès.", 'session');
				header('location: '.WRoute::getDir().'admin/user/');
			} else {
				$this->view->del($userid);
				$this->view->render('del');
			}
		} else {
			$this->liste();
		}
	}
	
	protected function cat() {
		// Préparation tri colonnes
		$args = WRoute::getArgs();
		if (isset($args[1])) {
			$sortData = explode('-', $args[1]);
		} else {
			$sortData = array();
		}
		$sortBy = empty($sortData) ? '' : array_shift($sortData);
		$sens = empty($sortData) ? '' : $sortData[0];
		
		/**
		 * Formulaire pour l'AJOUT d'une catégorie
		 */
		$data = WRequest::getAssoc(array('name'));
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$erreurs = array();
			
			if (empty($data['name'])) {
				$erreurs[] = "Il manque un nom à la catégorie.";
			}
			
			// Niveaux admin
			list($type, $access, $level) = WRequest::get(array('type', 'access', 'level'));
			if ($type == 'all') {
				$data['access'] = 'all';
			} else if ($type == 'perso') {
				$a = array();
				foreach ($access as $key => $v) {
					if (isset($level[$key])) {
						$a[] = $key.'|'.intval($level[$key]);
					} else {
						$a[] = $key.'|0';
					}
				}
				$data['access'] = implode(',', $a);
			} else {
				$data['access'] = '';
			}
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
			} else {
				if ($this->model->createCat($data)) {
					WNote::success("Catégorie ajoutée", "La catégorie <strong>".$data['name']."</strong> a été ajoutée avec succès.", 'assign');
				} else {
					WNote::error("Erreur lors de l'ajout", "Une erreur inconnue s'est produite.", 'assign');
				}
			}
		}
		
		/**
		 * Formulaire pour l'EDITION d'une catégorie
		 */
		$data = WRequest::getAssoc(array('idEdit', 'nameEdit', 'accessEdit'));
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$id = intval($data['idEdit']);
			unset($data['idEdit']);
			$erreurs = array();
			
			if (empty($data['nameEdit'])) {
				$erreurs[] = "Le nom de la catégorie est vide.";
			}
			
			// Niveaux admin
			list($type, $access, $level) = WRequest::get(array('typeEdit', 'accessEdit', 'levelEdit'));
			if ($type == 'all') {
				$data['accessEdit'] = 'all';
			} else if ($type == 'perso') {
				$a = array();
				foreach ($access as $key => $v) {
					if (isset($level[$key])) {
						$a[] = $key.'|'.intval($level[$key]);
					} else {
						$a[] = $key.'|0';
					}
				}
				$data['accessEdit'] = implode(',', $a);
			} else {
				$data['accessEdit'] = '';
			}
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
			} else {
				if ($this->model->updateCat($id, $data)) {
					WNote::success("Catégorie éditée", "La catégorie <strong>".$data['nameEdit']."</strong> a été éditée avec succès.", 'assign');
				} else {
					WNote::error("Erreur lors de l'édition", "Une erreur inconnue s'est produite.", 'assign');
				}
			}
		}
		
		$this->view->cat($sortBy, $sens);
		$this->view->render('cat');
	}
	
	protected function cat_del() {
		$id = $this->getId();
		$this->model->deleteCat($id);
		WNote::success("Suppression d'une catégorie", "La catégorie a été supprimée avec succès.", 'session');
		header('location: '.WRoute::getDir().'admin/user/cat/');
	}
}

?>
