<?php
/**
 * User Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserAdminView is the Admin View of the User Application.
 *
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class UserAdminView extends WView {
	public function __construct() {
		parent::__construct();

		// CSS for all views
		$this->assign('css', '/apps/user/admin/css/user.css');
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function users(array $model) {
		// SortingHelper Helper
		$this->assign($model['sorting_tpl']);

		// Users data
		$this->assign('users', $model['users']);
		$this->assign('groups', $model['groups']);
		$this->assign('stats', $model['stats']);

		// Get users waiting for validation
		$this->assign('users_waiting', $model['users_waiting']);
		if (!empty($model['users_waiting'])) {
			$this->assign('require', 'apps!user/admin_check');
		}

		// Treat filters
		$subURL = "";
		foreach ($model['filters'] as $k => $v) {
			if (!empty($v)) {
				$subURL .= $k."=".$v."&";
			}
		}
		if (!empty($subURL)) {
			$subURL = '?'.substr($subURL, 0, -1);
		}
		$this->assign('subURL', $subURL);
		$this->assign($model['filters']);

		$pagination = WHelper::load('pagination', array(
			$model['stats']['request'],
			$model['per_page'],
			$model['current_page'],
			'/admin/user/users/'.$model['sorting_vars'][0].'-'.strtolower($model['sorting_vars'][1]).'-%d/'.$subURL)
		);
		$this->assign('pagination', $pagination->getHTML());
	}

	/**
	 * Setting up the add/edit form
	 *
	 * @param array $model
	 */
	public function user_form(array $model) {
		// Display a warning message when user edits its own account
		if (!empty($model['user_data']) && isset($_SESSION['userid']) && $model['user_data']['id'] == $_SESSION['userid']) {
			WNote::info('user_edit_own', WLang::get('Warning: you are about to edit your own account. The changes will be applied immediatly.'));
		}

		// Displays a message for user under validation
		if (!empty($model['user_data']) && $model['user_data']['valid'] == 2) {
			WNote::info('user_validating_account', WLang::get('Submitting this form, this user account will be validated and the user will be notified by email.'));
		}

		// Setup the form
		$this->assign('require', 'apps!user/access_form');
		$this->assign('groups', $model['groupes']);
		$this->assign('apps', $model['apps']);
		$this->assign('default_admin', $model['default_admin']);

		$this->assignDefault(array(
			'id'            => 0,
			'nickname'      => '',
			'email'         => '',
			'firstname'     => '',
			'lastname'      => '',
			'groupe'        => 0,
			'access'        => '',
			'last_activity' => '',
			'created_date'  => ''
		), !empty($model['user_data']) ? $model['user_data'] : $model['post_data']);

		$this->setTemplate('user_form.html');
	}

	/**
	 * Handles the add view: triggers the user_form view with add setup.
	 *
	 * @param array $model
	 */
	public function add(array $model) {
		$this->user_form($model);
	}

	/**
	 * Handles the edit view: triggers the user_form view with edit setup.
	 *
	 * @param array $model
	 */
	public function edit(array $model) {
		$this->user_form($model);
	}

	/**
	 * Prepares a form to check if the user really wants to delete an account.
	 *
	 * @param array $model
	 */
	public function delete(array $model) {
		$this->assign('nickname', $model['nickname']);
		$this->assign('confirm_delete_url', "/admin/user/delete/".$model['id']);
	}

	/**
	 * Prepares the listing of all the groups in the database.
	 *
	 * @param array $model
	 */
	public function groups(array $model) {
		$this->assign('require', 'apps!user/groups');
		$this->assign($model['sorting_tpl']);

		$this->assign('groups', $model['groups']);
		$this->assign('apps', $model['apps']);
		$this->assign('default_admin', $model['default_admin']);
	}

	/**
	 * Group difference form.
	 *
	 * @param array $model
	 */
	public function group_diff(array $model) {
		$this->assign($model);
	}

	/**
	 * Prepares a form to check if the user really wants to delete a group.
	 *
	 * @param array $model
	 */
	public function group_del(array $model) {
		$this->assign('group_name', $model['name']);
		$this->assign('confirm_delete_url', '/admin/user/group_del/'.$model['id']);
	}

	/**
	 * Prepares the config view.
	 *
	 * @param array $config
	 */
	public function config(array $config) {
		$this->assign('config', $config);
	}
}

?>
