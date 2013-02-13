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
 * @version 0.3-06-02-2013
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
		$firstArg = array_shift($args);
		if ($firstArg == 'listing') {
			$firstArg = array_shift($args);
		}
		$sortData = explode('-', $firstArg);
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
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type', 'access'));
			if (!in_array(null, $data, true)) {
				$errors = array();
				
				// Check nickname availabililty
				if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
					$errors[] = WLang::_($e);
				}
				
				// Matching passwords
				if (!empty($data['password']) || !empty($data['password_conf'])) {
					if ($data['password'] === $data['password_conf']) {
						$data['password'] = sha1($data['password']);
					} else {
						$errors[] = WLang::_('error_password_not_matching');
					}
				} else {
					$errors[] = WLang::_('error_no_password');
				}
				
				// Email availabililty
				if (($e = $this->model->checkEmail($data['email'])) !== true) {
					$errors[] = WLang::_($e);
				}
				
				// User access rights
				if ($data['type'] == 'all') {
					$data['access'] = 'all';
				} else if ($data['type'] == 'none') {
					$data['access'] = '';
				} else { // Custom access
					$access = '';
					foreach ($data['access'] as $app => $perms) {
						$perms = array_keys($perms);
						if (!empty($perms)) {
							$access .= $app.'['.implode('|', $perms).'],';
						}
					}
					$access = substr($access, 0, -1);
					$data['access'] = $access;
				}
				unset($data['type']);
				
				if (empty($errors)) {
					// User creation
					if ($this->model->createUser($data)) {
						// Send email if requested
						if (!empty($data['email']) && WRequest::get('emailwarning') == 'on') {
							$mail = WHelper::load('phpmailer');
							$mail->CharSet = 'utf-8';
							$mail->From = WConfig::get('config.email');
							$mail->FromName = WConfig::get('config.site_name');
							$mail->Subject = WLang::get('user_register_email_subject', WConfig::get('config.site_name'));
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
						WNote::success('user_created', WLang::get('user_created', $data['nickname']));
						header('location: '.WRoute::getDir().'/admin/user/');
						return;
					} else {
						WNote::error('user_not_created', WLang::get('user_not_created', $data['nickname']));
					}
				} else {
					WNote::error('data_errors', implode("<br />\n", $errors));
				}
			} else {
				WNote::error('bad_data', WLang::get('bad_data'));
			}
		}
		$this->view->add($data);
		$this->view->render('user_form');
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
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type', 'access'));
			$update_data = array();
			$errors = array();
			
			// Get old user data
			$db_data = $this->model->getUser($userid);
			
			// Nickname change
			if ($data['nickname'] != $db_data['nickname']) {
				if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
					$errors[] = WLang::_($e);
				} else {
					$update_data['nickname'] = $data['nickname'];
				}
			}
			
			// Password
			if (!empty($data['password']) || !empty($data['password_conf'])) {
				if ($data['password'] == $data['password_conf']) {
					$password_hash = sha1($data['password']);
					if ($password_hash != $db_data['password']) {
						$update_data['password'] = $password_hash;
					}
				} else {
					$errors[] = WLang::get('error_password_not_matching');
				}
			}
			
			// Email
			if ($data['email'] != $db_data['email']) {
				if (($e = $this->model->checkEmail($data['email'])) !== true) {
					$errors[] = WLang::_($e);
				} else {
					$update_data['email'] = $data['emai'];
				}
			}
			
			// Firstname and Lastname
			if ($data['firstname'] != $db_data['firstname']) {
				$update_data['firstname'] = $data['firstname'];
			}
			if ($data['lastname'] != $db_data['lastname']) {
				$update_data['lastname'] = $data['lastname'];
			}
			
			// Group
			if ($data['groupe'] != $db_data['groupe']) {
				$update_data['groupe'] = intval($data['groupe']);
			}
			
			// User access rights
			if ($data['type'] == 'all') {
				$access = 'all';
			} else if ($data['type'] == 'none') {
				$access = '';
			} else { // Custom access
				$access = '';
				foreach ($data['access'] as $app => $perms) {
					$perms = array_keys($perms);
					if (!empty($perms)) {
						$access .= $app.'['.implode('|', $perms).'],';
					}
				}
				$access = substr($access, 0, -1);
			}
			if ($access != $db_data['access']) {
				$update_data['access'] = $access;
			}
			
			if (empty($errors)) {
				// Update database
				if ($this->model->updateUser($userid, $update_data)) {
					// Reload session if account was auto-edited
					if ($userid == $_SESSION['userid']) {
						WSystem::getSession()->reloadSession($userid);
					}
					
					WNote::success('user_edited', WLang::get('user_edited', $data['nickname']));
					header('location: '.WRoute::getDir().'/admin/user/edit/'.$userid);
					return;
				} else {
					WNote::error('user_not_edited', WLang::get('user_not_edited', $data['nickname']));
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		$this->view->edit($userid);
		$this->view->render('user_form');
	}
	
	/**
	 * Deletes a user
	 */
	protected function del() {
		$userid = $this->getId();
		if (!$this->model->validId($userid)) {
			WNote::error('user_not_found', WLang::get('user_not_found'));
			return;
		}
		if (WRequest::get('confirm', null, 'POST') === '1') {
			$this->model->deleteUser($userid);
			WNote::success('user_deleted', WLang::get('user_deleted'));
			header('location: '.WRoute::getDir().'/admin/user/');
		} else {
			$this->view->del($userid);
			$this->view->render('del');
		}
	}
	
	/**
	 * Groups listing/add/edit action
	 */
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
		
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('id', 'name', 'type', 'access'), null, 'POST');
			$errors = array();
			
			if (empty($data['name'])) {
				$errors[] = WLang::get('group_name_empty');
			}
			
			// User access rights
			if ($data['type'] == 'all') {
				$data['access'] = 'all';
			} else if ($data['type'] == 'none') {
				$data['access'] = '';
			} else { // Custom access
				$access = '';
				foreach ($data['access'] as $app => $perms) {
					$perms = array_keys($perms);
					if (!empty($perms)) {
						$access .= $app.'['.implode('|', $perms).'],';
					}
				}
				$access = substr($access, 0, -1);
				$data['access'] = $access;
			}
			
			if (empty($errors)) {
				$db_success = false;
				if (empty($data['id'])) { // Adding a group
					if ($this->model->createGroup($data)) {
						WNote::success('group_added', WLang::get('group_added', $data['name']));
						$db_success = true;
					}
				} else if ($this->model->updateGroup($data['id'], $data)) { // Editing a group
					WNote::success('group_edited', WLang::get('group_edited', $data['name']));
					$db_success = true;
				}
				if (!$db_success) {
					WNote::error('group_not_added', WLang::get('group_not_added'));
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		$this->view->groups_listing($sortBy, $sens);
		$this->view->render('groups_listing');
	}
	
	/**
	 * Deletes a group
	 * 
	 * @todo remove from the group deleted all the users who belonged to it
	 */
	protected function group_del() {
		$id = $this->getId();
		$this->model->deleteGroup($id);
		WNote::success('group_deleted', WLang::get('group_deleted'));
		header('location: '.WRoute::getDir().'/admin/user/groups/');
	}
}

?>
