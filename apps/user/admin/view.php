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
 * @version 0.4.0-26-04-2013
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
	public function listing(array $model) {
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
			'/admin/user/listing/'.$model['sorting_vars'][0].'-'.strtolower($model['sorting_vars'][1]).'-%d/'.$subURL)
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
		if (!empty($model['user_data']) && $model['user_data']['id'] == $_SESSION['userid']) {
			WNote::info('user_edit_own', WLang::get('user_edit_own'));
		}

		// Displays a message for user under validation
		if (!empty($model['user_data']) && $model['user_data']['valid'] == 2) {
			WNote::info('user_validating_account', WLang::get('user_validating_account'));
		}

		// Setup the form
		$this->assign('require', 'apps!user/access_form');
		$this->assign('groups', $model['groupes']);
		$this->assign('admin_apps', $model['admin_apps']);

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

		$this->setTemplate('user_form');
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
		if (!empty($model['group_diff'])) {
			$this->group_diff($model);
			return;
		}

		$this->assign('require', 'apps!user/access_form');
		$this->assign('require', 'apps!user/groups');
		$this->assign($model['sorting_tpl']);

		$this->assign('groups', $model['groups']);
		$this->assign('admin_apps', $model['admin_apps']);

		$this->setTemplate('groups_listing');
	}

	/**
	 * Prepares the group difference form.
	 *
	 * Allows to customize user access when modifying group access.
	 *
	 * @param array $model
	 */
	public function group_diff(array $model) {
		$group_id = $model['group_id'];

		$this->assign('require', 'apps!user/access_form');
		$this->assign('require', 'apps!user/group_diff');

		$this->assign('admin_apps', $model['admin_apps']);
		$this->assign('group', $model['group']);
		$this->assign('new_name', $model['group_name']);
		$this->assign('new_access', $model['group_access']);

		$chars = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$alphabet = array();
		$count_custom = 0;
		$model = new UserAdminModel();
		foreach ($chars as $c) {
			if ($c == '#') {
				$alphabet['#'] = $model->countUsersWithCustomAccess(array('nickname' => 'REGEXP:^[^a-zA-Z]', 'groupe' => $group_id));
			} else {
				$alphabet[$c] = $model->countUsersWithCustomAccess(array('nickname' => $c.'%', 'groupe' => $group_id));
			}
			$count_custom += $alphabet[$c];
		}
		$this->assign('alphabet', $alphabet);
		$count_total = $model->countUsers(array('groupe' => $group_id));
		$this->assign('count_total', $count_total);
		$this->assign('count_custom', $count_custom);
		$this->assign('count_regular', $count_total-$count_custom);

		$this->setTemplate('group_diff');
	}

	/**
	 * Prepares a form to check if the user really wants to delete a group.
	 *
	 * @param array $model
	 */
	public function group_del(array $model) {
		$this->assign('group_name', $model['name']);
		$this->assign('confirm_delete_url', "/admin/user/group_del/".$model['id']);
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
