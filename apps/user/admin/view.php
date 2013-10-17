<?php
/**
 * User Application - Admin View - /apps/user/admin/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserAdminView is the Admin View of the User Application.
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class UserAdminView extends WView {
	private $model;
	
	public function __construct() {
		parent::__construct();
		
		// CSS for all views
		$this->assign('css', '/apps/user/admin/css/user.css');
	}
	
	/**
	 * Setting up the users listing view
	 */
	public function listing($model) {
		// SortingHelper Helper
		$sort = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());
		
		// Users data
		$this->assign('users', $model['users']);
		$this->assign('groups', $model['groups']);
		$this->assign('stats', $model['stats']);
		
		// Get users waiting for validation
		$this->assign('users_waiting', $model['users_waiting']);
		if (!empty($model['users_waiting'])) {
			$this->assign('js', '/apps/user/admin/js/admin_check.js');
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
		
		$pagination = WHelper::load('pagination', array($model['stats']['request'], $model['users_per_page'], $model['current_page'], '/admin/user/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'.$subURL));
		$this->assign('pagination', $pagination->getHTML());
	}
	
	/**
	 * Setup add form
	 */
	public function user_form($model) {
		if (empty($model['user_id'])) {
			$this->assign('add_form', true); // ADD form
		}
		
		// Display a warning message when user edits its own account
		if ($model['user_id'] == $_SESSION['userid']) {
			WNote::info('user_edit_own', WLang::get('user_edit_own'));
		}
		
		// Displays a message for user under validation
		if (!empty($model['user_data']) && $model['user_data']['valid'] == 2) {
			WNote::info('user_validating_account', WLang::get('user_validating_account'));
		}
		
		// Get admin apps
		$this->assign('admin_apps', $model['admin_apps']);
		
		// Setup the form
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('groups', $model['groupes']);
		$this->assign('user_home', WRoute::getBase().'/admin/user/');
		
		$default_model = array(
			'id' => 0,
			'nickname' => '', 
			'email' => '',
			'firstname' => '',
			'lastname' => '',
			'groupe' => 0,
			'access' => ''
		);
		$data = !empty($model['user_data']) ? $model['user_data'] : $model['post_data'];
		foreach ($default_model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
		
		$this->setTemplate('user_form');
	}
	
	public function add($model) {
		$this->user_form($model);
	}
	
	public function edit($model) {
		$this->user_form($model);
	}
	
	/**
	 * Checks if the user really wanted to delete an account
	 */
	public function del($model) {
		$this->assign('nickname', $model['user_data']['nickname']);
		$this->assign('confirm_delete_url', "/admin/user/del/".$model['user_id']);
	}
	
	/**
	 * Displays a groups listing
	 */
	public function groups($model) {
		if (!empty($model['group_diff'])) {
			$this->group_diff($model);
			return;
		}
		
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('js', '/apps/user/admin/js/groups.js');
		
		// Get admin apps
		$this->assign('admin_apps', $model['admin_apps']);
		
		// SortingHelper
		$this->assign($model['sortingHelper']->getTplVars());
		
		$this->assign('groups', $model['groups']);
		
		$this->setTemplate('groups_listing');
	}
	
	/**
	 * Displays the group difference form
	 * Allows to customize user access when modifying group access
	 */
	public function group_diff($model) {
		$group_id = $model['group_id'];
		
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('js', '/apps/user/admin/js/group_diff.js');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
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
	 * Checks if the user really wanted to delete a group
	 */
	public function group_del($model) {
		$this->assign('group_name', $model['group_data']['name']);
		$this->assign('confirm_delete_url', "/admin/user/group_del/".$model['group_id']);
	}
	
	/**
	 * Prepares the config view
	 */
	public function config($config) {
		$this->assign('config', $config);
	}
}

?>
