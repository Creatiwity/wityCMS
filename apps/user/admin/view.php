<?php
/**
 * User Application - Admin View - /apps/user/admin/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserAdminView is the Admin View of the User Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-15-02-2013
 */
class UserAdminView extends WView {
	private $model;
	
	public function __construct(UserAdminModel $model) {
		parent::__construct();
		$this->model = $model;
		// CSS for all views
		$this->assign('css', '/apps/user/admin/css/user.css');
	}
	
	/**
	 * Setting up the users listing view
	 */
	public function listing($sortBy, $sens, $currentPage, $filters) {
		$n = 30; // number of users per page
		
		// SortingHelper Helper
		$sortingHelper = WHelper::load('SortingHelper', array(array('id', 'nickname', 'email', 'date', 'groupe', 'last_activity'), 'date', 'DESC'));
		$sort = $sortingHelper->getSorting($sortBy, $sens);
		
		// Register sorting vars to be displayed in the head of the sorting table
		$this->assign($sortingHelper->getTplVars());
		
		// Treat filters
		$subURL = "";
		foreach ($filters as $k => $v) {
			// Cleanup filters
			if (!empty($v)) {
				$subURL .= $k."=".$v."&";
			}
		}
		if (!empty($subURL)) {
			$subURL = '?'.substr($subURL, 0, -1);
		}
		$this->assign('subURL', $subURL);
		$this->assign($filters);
		
		// Get the user groups
		$this->assign('groups', $this->model->getGroupsList());
		
		// Assign main data
		$data = $this->model->getUsersList(($currentPage-1)*$n, $n, $sort[0], $sort[1] == 'ASC', $filters);
		$this->assign('users', $data);
		
		// Get users waiting for validation
		$users_waiting = $this->model->getUsersList(0, 0, $sort[0], $sort[1] == 'ASC', array('valid' => 2));
		$this->assign('users_waiting', $users_waiting);
		if (!empty($users_waiting)) {
			$this->assign('js', '/apps/user/admin/js/admin_check.js');
			// $this->assign('js', '/themes/system/js/jquery-1.8.1.min.js');
		}
		
		// Generate the pagination to browse data
		$count = $this->model->countUsers($filters);
		$pagination = WHelper::load('pagination', array($count, $n, $currentPage, '/admin/user/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'.$subURL));
		$this->assign('pagination', $pagination->getHTML());
		$this->assign('total', $count);
	}
	
	/**
	 * Setup the add/edit form
	 */
	private function fillForm($data) {
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('groups', $this->model->getGroupsList());
		$this->assign('user_home', WRoute::getBase().'/admin/user/');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
		
		$model = array(
			'id' => 0,
			'nickname' => '', 
			'email' => '',
			'firstname' => '',
			'lastname' => '',
			'groupe' => 0,
			'access' => ''
		);
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	/**
	 * Setup add form
	 */
	public function add($data = array()) {
		$this->fillForm($data);
		$this->assign('add_form', true);
	}
	
	/**
	 * Setup edit form
	 */
	public function edit($userid) {
		if ($userid == $_SESSION['userid']) {
			WNote::info('user_edit_own', WLang::get('user_edit_own'));
		}
		$data = $this->model->getUser($userid);
		if ($data['valid'] == 2) {
			WNote::info('user_validating_account', WLang::get('user_validating_account'));
		}
		$this->fillForm($data);
	}
	
	/**
	 * Verifies if the user really wanted to delete an account
	 */
	public function del($userid) {
		$data = $this->model->getUser($userid);
		$this->assign('nickname', $data['nickname']);
	}
	
	/**
	 * Displays a groups listing
	 */
	public function groups_listing($sortBy, $sens) {
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('js', '/apps/user/admin/js/groups.js');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
		
		// AdminStyle Helper
		$dispFields = array('name', 'users_count');
		$adminStyle = WHelper::load('SortingHelper', array($dispFields, 'name'));
		$sort = $adminStyle->getSorting($sortBy, $sens); // sorting vars
		
		// Enregistrement des variables de classement
		$this->assign($adminStyle->getTplVars());
		
		$data = $this->model->getGroupsListWithCount($sort[0], $sort[1] == 'ASC');
		$this->assign('groups', $data);
	}
	
	/**
	 * Displays the group difference form
	 * Allows to customize user access when modifying group access
	 */
	public function group_diff($groupid, $new_name, $new_access) {
		$this->assign('js', '/apps/user/admin/js/access_form.js');
		$this->assign('js', '/apps/user/admin/js/group_diff.js');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
		$this->assign('group', $this->model->getGroup($groupid));
		$this->assign('new_name', $new_name);
		$this->assign('new_access', $new_access);
		
		$chars = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$alphabet = array();
		$count_custom = 0;
		foreach ($chars as $c) {
			if ($c == '#') {
				$alphabet['#'] = $this->model->countUsersWithCustomAccess(array('nickname' => 'REGEXP:^[^a-zA-Z]', 'groupe' => $groupid));
			} else {
				$alphabet[$c] = $this->model->countUsersWithCustomAccess(array('nickname' => $c.'%', 'groupe' => $groupid));
			}
			$count_custom += $alphabet[$c];
		}
		$this->assign('alphabet', $alphabet);
		$count_total = $this->model->countUsers(array('groupe' => $groupid));
		$this->assign('count_total', $count_total);
		$this->assign('count_custom', $count_custom);
		$this->assign('count_regular', $count_total-$count_custom);
		
		$this->render('group_diff');
	}
	
	/**
	 * Prepares the config view
	 */
	public function config($config) {
		$this->assign('config', $config);
		$this->render('config');
	}
}

?>