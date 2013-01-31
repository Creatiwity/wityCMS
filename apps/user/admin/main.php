<?php
/**
 * User Application - Admin Controller - /apps/user/admin/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserAdminController is the Admin Controller of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-02-02-2012
 */
class UserAdminController extends WController {
	/**
	 * @var Instance of UserAdminModel
	 */
	private $model;
	
	public function __construct() {
		include_once 'model.php';
		$this->model = new UserAdminModel();
		
		include_once 'view.php';
		$this->setView(new UserAdminView($this->model));
	}
	
	/**
	 * Gets the Id given in the URL
	 * 
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
	
	/**
	 * List action handler
	 * Displays a list of users in the database
	 */
	protected function listing() {
		// Sorting criterias given by URL
		$args = WRoute::getArgs();
		$sortData = explode('-', array_shift($args));
		$sortBy = empty($sortData) ? '' : array_shift($sortData);
		$sens = empty($sortData) ? '' : array_shift($sortData);
		$page = empty($sortData) ? 1 : $sortData[0];
		
		// Filters
		$filters = WRequest::getAssoc(array('nickname', 'email', 'firstname', 'lastname', 'groupe'));
		
		$this->view->listing($sortBy, $sens, $page, $filters);
		$this->view->render('listing');
	}
	
	/**
	 * Create a user
	 */
	protected function add() {
		$data = array();
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'groupe'));
			if (!in_array(null, $data, true)) {
				$errors = array();
				
				// Check nickname availabililty
				if (empty($data['nickname'])) {
					$errors[] = "No nickname given.";
				} else if (!$this->model->nicknameAvailable($data['nickname'])) {
					$errors[] = "Nickname already in use.";
				}
				
				// Matching passwords
				if (!empty($data['password'])) {
					if ($data['password'] === $data['password_conf']) {
						$data['password'] = sha1($data['password']);
					} else {
						$errors[] = "The password given is not the same as the password confirmation.";
					}
				} else {
					$errors[] = "No password given.";
				}
				
				// Email availabililty
				if (!empty($data['email']) && !preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $data['email'])) {
					$errorss[] = "The email given is not valid.";
				} else if (!$this->model->emailAvailable($data['email'])) {
					$errors[] = "The email given is already in use.";
				}
				
				// Niveaux admin
				/*
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
				*/
				
				if (empty($errors)) {
					// User creation
					if ($this->model->createUser($data)) {
						// Send email if requested
						if (!empty($data['email']) && WRequest::get('emailwarning') == 'on') {
							$mail = WHelper::load('phpmailer');
							$mail->CharSet = 'utf-8';
							$mail->From = WConfig::get('config.email');
							$mail->FromName = WConfig::get('config.site_name');
							$mail->Subject = sprintf("Account creation on %s", WConfig::get('config.site_name'));
							$mail->Body = 
"Bonjour,
<br /><br />
Un compte utilisateur vient de vous être créé sur le site ".WConfig::get('config.site_name').".<br /><br />
Pour vous connecter, rendez-vous à l'adresse <a href=\"".WRoute::getBase()."/user/login/\">".WRoute::getBase()."/user/login/</a>.
<br /><br />
Voici vos données de connexion :<br />
<strong>Identifiant :</strong> ".$data['nickname']."<br />
<strong>Mot de passe :</strong> ".$data['password_conf']."
<br /><br />
Ces informations sont personnelles.<br />
Pour tout changement, rendez-vous sur l'espace membre.
<br /><br />
".WConfig::get('config.site_name')."
<br /><br />
--------------<br />
Ceci est un message automatique.";
							$mail->IsHTML(true);
							$mail->AddAddress($data['email']);
							$mail->Send();
							unset($mail);
						}
						WNote::success('user_created', sprintf("The user %s was created successfully.", $data['nickname']));
						header('location: '.WRoute::getDir().'/admin/user/');
						return;
					} else {
						WNote::error('user_not_created', "An unknown error occured when trying to create your account in the database.");
					}
				} else {
					WNote::error('data_errors', implode("<br />\n", $errors));
				}
			} else {
				WNote::error('bad_data', "Please, fill in all the required fields.");
			}
		}
		$this->view->add($data);
		$this->view->render('add');
	}
	
	/**
	 * Edits a user in the database
	 */
	protected function edit() {
		$userid = $this->getId();
		if (!$this->model->validId($userid)) {
			WNote::error('user_not_found', "The user requested does not exist.");
			header('location: '.WRoute::getDir().'/admin/user/');
			return;
		}
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'groupe', 'access'));
			$update_data = array();
			$errors = array();
			
			// Get old user data
			$db_data = $this->model->getUser($userid);
			
			// Nickname change
			if ($data['nickname'] != $db_data['nickname']) {
				if ($e = $this->model->checkNickname($data['nickname']) !== true) {
					$errors[] = $e;
				} else {
					$update_data['nickname'] = $data['nickname'];
				}
			}
			
			// Password
			$password_hash = sha1($data['password']);
			if ($password_hash != $db_data['password']) {
				if ($data['password'] == $data['password_conf']) {
					$update['password'] = $password_hash;
				} else {
					$errors[] = "The password given is not the same as the password confirmation.";
				}
			}
			
			// Email
			if ($data['email'] != $db_data['email']) {
				if ($e = $this->model->checkEmail($data['email']) !== true) {
					$errors[] = $e;
				} else {
					$update_data['email'] = $data['emai'];
				}
			}
			
			// Group
			if ($data['groupe'] != $db_data['groupe']) {
				$update_data['groupe'] = intval($data['groupe']);
			}
			
			// Amin access
			/*list($type, $access, $level) = WRequest::get(array('type', 'access', 'level'));
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
			}*/
			
			if (empty($errors)) {
				// Update database
				if ($this->model->updateUser($userid, $update_data)) {
					WNote::success('user_edited', sprintf("The user <strong>%s</strong> was edited successfully.", $data['nickname']));
					header('location: '.WRoute::getDir().'/admin/user/edit/'.$userid);
				} else {
					WNote::error('user_not_edited', "An unknown error occured.");
					$this->view->edit($userid);
					$this->view->render('add');
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
				$this->view->edit($userid);
				$this->view->render('edit');
			}
		} else {
			$this->view->edit($userid);
			$this->view->render('edit');
		}
	}
	
	/**
	 * Deletes a user
	 */
	protected function del() {
		$userid = $this->getId();
		if (!$this->model->validId($userid)) {
			WNote::error('user_not_found', "The user requested does not exist.");
			return;
		}
		if (WRequest::get('confirm', null, 'POST') === '1') {
			$this->model->deleteUser($userid);
			WNote::success('user_deleted', "L'utilisateur a été supprimé avec succès.");
			header('location: '.WRoute::getDir().'/admin/user/');
		} else {
			$this->view->del($userid);
			$this->view->render('del');
		}
	}
	
	protected function groups() {
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
				WNote::error('data_errors', implode("<br />\n", $erreurs));
			} else {
				if ($this->model->createGroup($data)) {
					WNote::success('cat_added', "La catégorie <strong>".$data['name']."</strong> a été ajoutée avec succès.");
				} else {
					WNote::error('cat_not_added', "Une erreur inconnue s'est produite.");
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
				WNote::error('data_errors', implode("<br />\n", $erreurs));
			} else {
				if ($this->model->updateGroup($id, $data)) {
					WNote::success('cat_edited', "La catégorie <strong>".$data['nameEdit']."</strong> a été éditée avec succès.");
				} else {
					WNote::error('cat_not_edited', "Une erreur inconnue s'est produite.");
				}
			}
		}
		
		$this->view->groups_listing($sortBy, $sens);
		$this->view->render('groups_listing');
	}
	
	protected function group_del() {
		$id = $this->getId();
		$this->model->deleteGroup($id);
		WNote::success('group_deleted', "The group selected was deleted successfully.");
		header('location: '.WRoute::getDir().'/admin/user/groups/');
	}
}

?>
