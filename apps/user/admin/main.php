<?php
/**
 * User Application - Admin Controller - /apps/user/admin/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserAdminController is the Admin Controller of the User Application.
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class UserAdminController extends WController {
	/**
	 * List action handler
	 * Displays a list of users in the database
	 */
	protected function listing($params) {
		$n = 30; // number of users per page
		
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
		$sort_by = '';
		$sens = 'DESC';
		$page = 1;
		if (!empty($params[0])) {
			$count = sscanf(str_replace('-', ' ', $params[0]), '%s %s %d', $sort_by, $sens, $page_crit);
			if ($page > 1) {
				$page = $page_crit;
			}
		}
		
		// SortingHelper
		$sortingHelper = WHelper::load('SortingHelper', array(array('id', 'nickname', 'email', 'date', 'groupe', 'last_activity'), 'date', 'DESC'));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		// Filters
		$filters = WRequest::getAssoc(array('nickname', 'email', 'firstname', 'lastname', 'groupe'));
		$has_filter = false;
		foreach ($filters as $name => $value) {
			if (empty($value)) {
				$has_filter = true;
			}
		}
		
		// Define model
		$model = array(
			'users' => $this->model->getUsersList(($page-1)*$n, $n, $sort[0], $sort[1] == 'ASC', $filters),
			'users_waiting' => $this->model->getUsersList(0, 0, $sort[0], $sort[1] == 'ASC', array('valid' => 2)),
			'groups' => $this->model->getGroupsList(),
			'stats' => array(),
			'current_page' => $page,
			'users_per_page' => $n,
			'sortingHelper' => $sortingHelper,
			'filters' => $filters
		);
		
		// Users count
		$model['stats']['total'] = $this->model->countUsers();
		$model['stats']['request'] = $model['stats']['total'];
		if ($has_filter) {
			$model['stats']['filtered'] = $this->model->countUsers($filters);
			$model['stats']['request'] = $model['stats']['filtered'];
		}
		
		return $model;
	}
	
	/**
	 * Automatically creates the user or updates it depending on the data given
	 * If needed, the User form is displayed
	 */
	protected function user_form($user_id = null, $db_data = array()) {
		$add_case = empty($user_id);
		$post_data = array();
		
		if (!empty($_POST)) {
			$post_data = WRequest::getAssoc(array('nickname', 'password', 'password_conf', 'email', 'firstname', 'lastname', 'groupe', 'type', 'access'));
			$errors = array();
			
			// Check nickname availabililty
			if ($add_case || $post_data['nickname'] != $db_data['nickname']) {
				if (($e = $this->model->checkNickname($post_data['nickname'])) !== true) {
					$errors[] = WLang::get($e);
				}
			} else {
				unset($post_data['nickname']);
			}
			
			// Matching passwords
			if (!empty($post_data['password']) || !empty($post_data['password_conf'])) {
				if ($post_data['password'] === $post_data['password_conf']) {
					$password_original = $post_data['password'];
					$post_data['password'] = sha1($post_data['password']);
					if (!$add_case && $post_data['password'] == $db_data['password']) {
						unset($post_data['password']); // don't change password if it's the same
					}
				} else {
					$errors[] = WLang::get('error_password_not_matching');
				}
			} else if ($add_case) {
				$errors[] = WLang::get('error_no_password');
			} else {
				unset($post_data['password']);
			}
			unset($post_data['password_conf']);
			
			// Email availabililty
			if ($add_case || $post_data['email'] != $db_data['email']) {
				if (($e = $this->model->checkEmail($post_data['email'])) !== true) {
					$errors[] = WLang::get($e);
				}
			}
			
			// Firstname and Lastname
			if (!$add_case && $post_data['firstname'] == $db_data['firstname']) {
				unset($post_data['firstname']);
			}
			if (!$add_case && $post_data['lastname'] == $db_data['lastname']) {
				unset($post_data['lastname']);
			}
			
			// Groupe
			$post_data['groupe'] = intval($post_data['groupe']);
			if (!$add_case && $post_data['groupe'] == $db_data['groupe']) {
				unset($post_data['groupe']);
			}
			
			// User access rights
			$post_data['access'] = $this->model->treatAccessData($post_data['type'], $post_data['access']);
			if (!$add_case && $post_data['access'] == $db_data['access']) {
				unset($post_data['access']);
			}
			unset($post_data['type']);
			
			if (empty($errors)) {
				if ($add_case) { // ADD case
					if ($this->model->createUser($post_data)) {
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
<strong>Identifiant :</strong> ".$post_data['nickname']."<br />
<strong>Mot de passe :</strong> ".$password_original."
<br /><br />
Ces informations sont personnelles.<br />
Pour tout changement, rendez-vous sur l'espace membre.
<br /><br />
".WConfig::get('config.site_name')."
<br /><br />
--------------<br />
Ceci est un message automatique.";
							$mail->IsHTML(true);
							$mail->AddAddress($post_data['email']);
							$mail->Send();
							unset($mail);
						}
						
						WNote::success('user_created', WLang::get('user_created', $post_data['nickname']));
						$this->view->setHeader('Location', WRoute::getDir().'/admin/user/');
					} else {
						WNote::error('user_not_created', WLang::get('user_not_created', $post_data['nickname']));
					}
				} else { // EDIT case
					if ($this->model->updateUser($user_id, $post_data)) {
						// Reload session if account was auto-edited
						if ($user_id == $_SESSION['userid']) {
							WSystem::getSession()->reloadSession($user_id);
						}
						
						WNote::success('user_edited', WLang::get('user_edited', $db_data['nickname']));
						$this->view->setHeader('Location', WRoute::getDir().'/admin/user/edit/'.$user_id);
					} else {
						WNote::error('user_not_edited', WLang::get('user_not_edited', $db_data['nickname']));
					}
				}
			} else {
				WNote::error('user_data_errors', implode("<br />\n", $errors));
			}
		}
		
		// Model
		return array(
			'user_id'   => $user_id, 
			'user_data' => $db_data,
			'post_data' => $post_data,
			'groupes'   => $this->model->getGroupsList(),
			'admin_apps' => $this->getAdminApps()
		);
	}
	
	/**
	 * Creates a user
	 */
	protected function add() {
		return $this->user_form();
	}
	
	/**
	 * Edits a user in the database
	 */
	protected function edit($params) {
		$user_id = intval(array_shift($params));
		
		if (empty($user_id) || !$this->model->validId($user_id)) {
			WNote::error('user_not_found', WLang::get('user_not_found'));
			$this->view->setHeader('Location', WRoute::getDir().'/admin/user/');
			return false;
		}
		
		$user_data = $this->model->getUser($user_id);
		return $this->user_form($user_id, $user_data);
	}
	
	/**
	 * Deletes a user
	 */
	protected function del($params) {
		$user_id = intval(array_shift($params));
		
		if ($user_id == $_SESSION['userid']) {
			WNote::error('user_self_delete', WLang::get('user_self_delete'), 'display');
			return false;
		}
		
		if (empty($user_id) || !$this->model->validId($user_id)) {
			WNote::error('user_not_found', WLang::get('user_not_found'), 'display');
			return false;
		}
		
		if (WRequest::get('confirm', null, 'POST') === '1') {
			$this->model->deleteUser($user_id);
			WNote::success('user_deleted', WLang::get('user_deleted'));
			$this->view->setHeader('Location', WRoute::getDir().'/admin/user/');
		}
		
		return array(
			'user_id' => $user_id,
			'user_data' => $this->model->getUser($user_id)
		);
	}
	
	/**
	 * Groups listing/add/edit action
	 */
	protected function groups($params) {
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
						// There will be a change in group's default access affecting users
						if ($data['access'] != $db_data['access'] && $count_users > 0) {
							return array(
								'group_diff'   => true,
								'group_id'     => $data['id'],
								'group_name'   => $data['name'],
								'group_access' => $data['access'],
								'group'        => $db_data
							);
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
		
		// Préparation tri colonnes
		$sort_by = '';
		$sens = 'DESC';
		if (!empty($params[0])) {
			$count = sscanf(str_replace('-', ' ', $params[0]), '%s %s', $sort_by, $sens);
		}
		
		// SortingHelper
		$disp_fields = array('name', 'users_count');
		$sortingHelper = WHelper::load('SortingHelper', array($disp_fields, 'name'));
		$sort = $sortingHelper->findSorting($sort_by, $sens); // sorting vars
		
		$adminController = new AdminController();
		
		return array(
			'groups' => $this->model->getGroupsListWithCount($sort[0], $sort[1] == 'ASC'),
			'admin_apps' => $adminController->getAdminApps(),
			'sortingHelper' => $sortingHelper
		);
	}
	
	/**
	 * Deletes a group
	 */
	protected function group_del($params) {
		$group_id = intval(array_shift($params));
		
		if (!$this->model->validGroupId($group_id)) {
			WNote::error('group_not_found', WLang::get('group_not_found'), 'display');
			return false;
		}
		
		if (WRequest::get('confirm', null, 'POST') === '1') {
			$this->model->deleteGroup($group_id);
			$this->model->resetUsersInGroup($group_id);
			WNote::success('user_group_deleted', WLang::get('group_deleted'));
			$this->view->setHeader('Location', WRoute::getDir().'/admin/user/groups/');
		}
		
		return array(
			'group_id' => $group_id,
			'group_data' => $this->model->getGroup($group_id)
		);
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
		
		$this->view->setHeader('Location', WRoute::getDir().'/admin/user/groups');
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
		
		return $config;
	}
}

?>
