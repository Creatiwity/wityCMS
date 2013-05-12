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
 * @version 0.3-26-04-2013
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
		// Admin check
		$admin_check = WRequest::get('admin_check');
		if (!empty($admin_check)) {
			$notify = WRequest::get('notify');
			foreach ($admin_check as $user_id => $action) {
				$db_data = $this->model->getUser($user_id);
				if (isset($db_data['valid']) && $db_data['valid'] == 2) {
					if ($action == 'validate') {
						$this->model->updateUser($user_id, array('valid' => 1));
						// Send email notification
						if ($notify) {
							$this->model->sendEmail(
								$db_data['email'],
								WLang::get('user_account_validated_subject', WConfig::get('config.site_name')),
								str_replace(
									array('{site_name}', '{base}'),
									array(WConfig::get('config.site_name'), WRoute::getBase()),
									WLang::get('user_account_validated_email')
								)
							);
						}
						WNote::success('user_account_validated', WLang::get('user_account_validated', $db_data['nickname']));
					} else if ($action == 'refuse') {
						$config = $this->model->getConfig();
						if ($config['keep_users']) {
							$this->model->updateUser($user_id, array('valid' => 0));
						} else {
							$this->model->deleteUser($user_id);
						}
						// Send email notification
						if ($notify) {
							$this->model->sendEmail(
								$db_data['email'],
								WLang::get('user_account_refused_subject', WConfig::get('config.site_name')),
								str_replace(
									array('{site_name}', '{base}'),
									array(WConfig::get('config.site_name'), WRoute::getBase()),
									WLang::get('user_account_refused_email')
								)
							);
						}
						WNote::success('user_account_refused', WLang::get('user_account_refused', $db_data['nickname']));
					}
				} else {
					WNote::success('user_account_invalid', WLang::get('user_account_invalid', $db_data['nickname']));
				}
			}
		}
		
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
	}
	
	/**
	 * Automatically creates the user or updates it depending on the data given
	 * If needed, the User form is displayed
	 */
	protected function user_form($user_id = null, $db_data = array()) {
		$add_case = empty($user_id);
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type', 'access'));
			$errors = array();
			
			// Check nickname availabililty
			if ($add_case || $data['nickname'] != $db_data['nickname']) {
				if (($e = $this->model->checkNickname($data['nickname'])) !== true) {
					$errors[] = WLang::get($e);
				}
			} else {
				unset($data['nickname']);
			}
			
			// Matching passwords
			if (!empty($data['password']) || !empty($data['password_conf'])) {
				if ($data['password'] === $data['password_conf']) {
					$data['password'] = sha1($data['password']);
					if (!$add_case && $data['password'] == $db_data['password']) {
						unset($data['password']); // don't change password if it's the same
					}
				} else {
					$errors[] = WLang::get('error_password_not_matching');
				}
			} else if ($add_case) {
				$errors[] = WLang::get('error_no_password');
			}
			unset($data['password_conf']);
			
			// Email availabililty
			if ($add_case || $data['email'] != $db_data['email']) {
				if (($e = $this->model->checkEmail($data['email'])) !== true) {
					$errors[] = WLang::get($e);
				}
			}
			
			// Firstname and Lastname
			if (!$add_case && $data['firstname'] == $db_data['firstname']) {
				unset($data['firstname']);
			}
			if (!$add_case && $data['lastname'] == $db_data['lastname']) {
				unset($data['lastname']);
			}
			
			// Groupe
			$data['groupe'] = intval($data['groupe']);
			if (!$add_case && $data['groupe'] == $db_data['groupe']) {
				unset($data['groupe']);
			}
			
			// User access rights
			$data['access'] = $this->model->treatAccessData($data['type'], $data['access']);
			if (!$add_case && $data['access'] == $db_data['access']) {
				unset($data['access']);
			}
			unset($data['type']);
			
			if (empty($errors)) {
				if ($add_case) { // ADD case
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
						header('Location: '.WRoute::getDir().'/admin/user/');
						return;
					} else {
						WNote::error('user_not_created', WLang::get('user_not_created', $data['nickname']));
					}
				} else { // EDIT case
					if ($this->model->updateUser($user_id, $data)) {
						// Reload session if account was auto-edited
						if ($user_id == $_SESSION['userid']) {
							WSystem::getSession()->reloadSession($user_id);
						}
						
						WNote::success('user_edited', WLang::get('user_edited', $data['nickname']));
						header('Location: '.WRoute::getDir().'/admin/user/edit/'.$user_id);
						return;
					} else {
						WNote::error('user_not_edited', WLang::get('user_not_edited', $data['nickname']));
					}
				}
			} else {
				WNote::error('user_data_errors', implode("<br />\n", $errors));
			}
		}
		
		if ($add_case) {
			if (empty($data)) {
				$this->view->user_form();
			} else {
				$this->view->user_form(null, $data);
			}
		} else {
			$this->view->user_form($user_id, $db_data);
		}
	}
	
	/**
	 * Creates a user
	 */
	protected function add() {
		$this->user_form();
	}
	
	/**
	 * Edits a user in the database
	 */
	protected function edit() {
		$user_id = intval(WRoute::getArg(1));
		if ($this->model->validId($user_id)) {
			$db_data = $this->model->getUser($user_id);
			$this->user_form($user_id, $db_data);
		} else {
			WNote::error('user_not_found', WLang::get('user_not_found'));
			header('Location: '.WRoute::getDir().'/admin/user/');
		}
	}
	
	/**
	 * Deletes a user
	 */
	protected function del() {
		$user_id = intval(WRoute::getArg(1));
		if ($user_id != $_SESSION['userid']) {
			if ($this->model->validId($user_id)) {
				if (WRequest::get('confirm', null, 'POST') === '1') {
					$this->model->deleteUser($user_id);
					WNote::success('user_deleted', WLang::get('user_deleted'));
					header('Location: '.WRoute::getDir().'/admin/user/');
				} else {
					$this->view->del($user_id);
				}
			} else {
				WNote::error('user_not_found', WLang::get('user_not_found'), 'display');
			}
		} else {
			WNote::error('user_self_delete', WLang::get('user_self_delete'), 'display');
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
							$this->view->group_diff($data['id'], $data['name'], $data['access']);
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
	protected function group_diff() {
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
						$user_id = $user['id'];
						if (array_key_exists($user_id, $data['user'])) {
							$this->model->updateUser($user_id, array('access' => $data['new_access']));
						} else if (!empty($data['type'][$user_id])) {
							// Update with given access
							$access = $this->model->treatAccessData($data['type'][$user_id], isset($data['access'][$user_id]) ? $data['access'][$user_id] : array());
							if ($user['access'] != $access) {
								$this->model->updateUser($user_id, array('access' => $access));
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
	 * Used for ajax method in group_diff action.
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
	
	/**
	 * Configuration handler
	 */
	protected function config() {
		$data = WRequest::getAssoc(array('update', 'config'));
		$config = $this->model->getConfig();
		if ($data['update'] == 'true') {
			foreach ($config as $name => $value) {
				$config[$name] = intval(!empty($data['config'][$name]));
				$this->model->setConfig($name, $config[$name]);
			}
			WNote::success('user_config_updated', WLang::get('user_config_updated'));
		}
		$this->view->config($config);
	}
}

?>
