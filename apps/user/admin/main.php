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
 * @version 0.3-15-02-2013
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
	 * List action handler
	 * Displays a list of users in the database
	 */
	protected function listing() {
		// Sorting criterias given by URL
		$args = WRoute::getArgs();
		$criterias = array_shift($args);
		if ($criterias == 'listing') {
			$criterias = array_shift($args);
		}
		$count = sscanf(str_replace('-', ' ', $criterias), '%s %s %d', $sortBy, $sens, $page);
		if (empty($page) || $page <= 0) {
			$page = 1;
		}
		
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
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type'));
			if (!in_array(null, $data, true)) {
				$errors = array();
				
				// Check nickname availabililty
				if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
					$errors[] = WLang::get($e);
				}
				
				// Matching passwords
				if (!empty($data['password']) || !empty($data['password_conf'])) {
					if ($data['password'] === $data['password_conf']) {
						$data['password'] = sha1($data['password']);
					} else {
						$errors[] = WLang::get('error_password_not_matching');
					}
				} else {
					$errors[] = WLang::get('error_no_password');
				}
				
				// Email availabililty
				if (($e = $this->model->checkEmail($data['email'])) !== true) {
					$errors[] = WLang::get($e);
				}
				
				// User access rights
				$data['access'] = $this->model->treatAccessData($data['type'], WRequest::get('access'));
				unset($data['type']);
				
				if (empty($errors)) {
					// User creation
					if ($this->model->createUser($data)) {
						// Send email if requested
						if (WRequest::get('email_confirmation') == 'on') {
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
					WNote::error('user_data_errors', implode("<br />\n", $errors));
				}
			} else {
				WNote::error('user_bad_data', WLang::get('bad_data'));
			}
		}
		$this->view->add($data);
		$this->view->render('user_form');
	}
	
	/**
	 * Edits a user in the database
	 */
	protected function edit() {
		$userid = intval(WRoute::getArg(1));
		if (!$this->model->validId($userid)) {
			WNote::error('user_not_found', WLang::get('user_not_found'));
			header('location: '.WRoute::getDir().'/admin/user/');
			return;
		}
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type'));
			if (!in_array(null, $data, true)) {
				$update_data = array();
				$errors = array();
				
				// Get old user data
				$db_data = $this->model->getUser($userid);
				
				// Nickname change
				if ($data['nickname'] != $db_data['nickname']) {
					if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
						$errors[] = WLang::get($e);
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
						$errors[] = WLang::get($e);
					} else {
						$update_data['email'] = $data['email'];
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
				$access = $this->model->treatAccessData($data['type'], WRequest::get('access'));
				if ($access != $db_data['access']) {
					$update_data['access'] = $access;
				}
				
				// Valid
				if ($db_data['valid'] == 2) {
					$update_data['valid'] = 1;
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
					WNote::error('user_data_errors', implode("<br />\n", $errors));
				}
			} else {
				WNote::error('user_bad_data', WLang::get('bad_data'));
			}
		}
		$this->view->edit($userid);
		$this->view->render('user_form');
	}
	
	/**
	 * Deletes a user
	 */
	protected function del() {
		$userid = intval(WRoute::getArg(1));
		if ($userid != $_SESSION['userid']) {
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
		} else {
			WNote::error('user_self_delete', WLang::get('user_self_delete'));
			header('location: '.WRoute::getDir().'/admin/user/');
		}
	}
	
	/**
	 * Groups listing/add/edit action
	 */
	protected function groups() {
		// Préparation tri colonnes
		$args = WRoute::getArg(1);
		$count = sscanf(str_replace('-', ' ', $args), '%s %s', $sortBy, $sens);
		
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('id', 'name', 'type', 'access'), null, 'POST');
			$errors = array();
			
			if (empty($data['name'])) {
				$errors[] = WLang::get('group_name_empty');
			}
			
			// User access rights
			$data['access'] = $this->model->treatAccessData($data['type'], $data['access']);
			
			if (empty($errors)) {
				$db_success = false;
				if (empty($data['id'])) { // Adding a group
					if ($this->model->createGroup($data)) {
						WNote::success('user_group_added', WLang::get('group_added', $data['name']));
						$db_success = true;
					}
				} else { // Editing a group
					$db_data = $this->model->getGroup($data['id']);
					if (!empty($db_data)) {
						$count_users = $this->model->countUsers(array('groupe' => $data['id']));
						// There will be a change in default group access affecting users
						if ($data['access'] != $db_data['access'] && $count_users > 0) {
							$this->view->group_dif($data['id'], $data['name'], $data['access']);
							return;
						} else if ($this->model->updateGroup($data['id'], $data)) {
							WNote::success('user_group_edited', WLang::get('group_edited', $data['name']));
							$db_success = true;
						}
					}
				}
				if (!$db_success) {
					WNote::error('user_group_not_modified', WLang::get('group_not_modified'));
				}
			} else {
				WNote::error('user_data_errors', implode("<br />\n", $errors));
			}
		}
		$this->view->groups_listing($sortBy, $sens);
		$this->view->render('groups_listing');
	}
	
	/**
	 * Deletes a group
	 */
	protected function group_del() {
		$id = intval(WRoute::getArg(1));
		if (!empty($id)) {
			$this->model->deleteGroup($id);
			$this->model->resetUsersInGroup($id);
		}
		WNote::success('user_group_deleted', WLang::get('group_deleted'));
		header('location: '.WRoute::getDir().'/admin/user/groups/');
	}
	
	/**
	 * Makes the dif between old and new access to a group
	 */
	protected function group_dif() {
		// Retrieve post data
		$data = WRequest::getAssoc(array('groupid', 'new_name', 'old_access', 'new_access'));
		if (!in_array(null, $data, true)) {
			$data = array_merge($data, WRequest::getAssoc(array('apply_to_regular', 'apply_to_custom', 'user', 'type', 'access')));
			if ($data['apply_to_regular'] == 'on' && $data['apply_to_custom'] == 'on') {
				$this->model->updateUsers(array('access' => $data['new_access']), array('groupe' => $data['groupid']));
			} else {
				// Update all users having the old group access
				if ($data['apply_to_regular'] == 'on') {
					$this->model->updateUsers(array('access' => $data['new_access']), array('groupe' => $data['groupid'], 'access' => $data['old_access']));
				}
				// Update all users having custom group access
				if ($data['apply_to_custom'] == 'on' || sizeof($data['user']) == sizeof($data['type'])) {
					$this->model->updateUsers(array('access' => $data['new_access']), array('groupe' => $data['groupid'], 'access' => 'NOT:'.$data['old_access']));
				} else { // Custom update
					// Retrieve all custom users belonging to this group
					$users = $this->model->getUsersWithCustomAccess(array('groupe' => $data['groupid']));
					foreach ($users as $user) {
						$userid = $user['id'];
						if (array_key_exists($userid, $data['user'])) {
							$this->model->updateUser($userid, array('access' => $data['new_access']));
						} else if (!empty($data['type'][$userid])) {
							// Update with given access
							$access = $this->model->treatAccessData($data['type'][$userid], isset($data['access'][$userid]) ? $data['access'][$userid] : array());
							if ($user['access'] != $access) {
								$this->model->updateUser($userid, array('access' => $access));
							}
						}
					}
				}
			}
			
			// Update group with new access
			if ($this->model->updateGroup($data['groupid'], array('name' => $data['new_name'], 'access' => $data['new_access']))) {
				WNote::success('user_group_edited', WLang::get('group_edited', $data['new_name']));
			} else {
				WNote::error('user_group_not_modified', WLang::get('group_not_modified'));
			}
		}
		header('location: '.WRoute::getDir().'/admin/user/groups');
	}
	
	/**
	 * Display in a JSON format all the users whose nickname starts with a given letter.
	 * 
	 * Used for ajax method in group_dif action.
	 */
	protected function load_users_with_letter() {
		$letter = WRequest::get('letter');
		$groupid = intval(WRequest::get('groupe'));
		$json = '{';
		if (!empty($letter) && !empty($groupid)) {
			if ($letter == '#') {
				$users = $this->model->getUsersWithCustomAccess(array('nickname' => 'REGEXP:^[^a-zA-Z]', 'groupe' => $groupid));
			} else {
				$users = $this->model->getUsersWithCustomAccess(array('nickname' => $letter.'%', 'groupe' => $groupid));
			}
			foreach ($users as $user) {
				$json .= '"'.$user['id'].'": {"nickname": "'.addslashes($user['nickname']).'", "access": "'.$user['access'].'"},';
			}
			if (strlen($json) > 1) {
				$json = substr($json, 0, -1);
			}
		}
		$json .= '}';
		echo $json;
	}
	
	protected function config() {
		$data = WRequest::getAssoc(array('update', 'register', 'email_conf', 'summary', 'admin_check'));
		if ($data['update'] == 'true') {
			foreach ($data as $name => $value) {
				$this->model->setConfig($name, intval(!empty($value)));
			}
			WNote::success('user_config_updated', WLang::get('user_config_updated'));
		}
		$this->view->config($this->model->getConfig());
	}
}

?>
