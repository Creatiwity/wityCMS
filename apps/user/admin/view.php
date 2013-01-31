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
 * @version 0.3-02-02-2012
 */
class UserAdminView extends WView {
	private $model;
	
	public function __construct(UserAdminModel $model) {
		parent::__construct();
		$this->model = $model;
		
		$this->assign('css', '/apps/user/admin/css/user.css');
	}
	
	/**
	 * Setting up the users listing view
	 */
	public function listing($sortBy, $sens, $currentPage, $filters) {
		$n = 40; // 40 users per page
		
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
		
		// Generate the pagination to navigate data
		$count = $this->model->countUsers($filters);
		$pagination = WHelper::load('pagination', array($count, $n, $currentPage, '/admin/user/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'.$subURL));
		$this->assign('pagination', $pagination->getHtml());
		$this->assign('total', $count);
	}
	
	/**
	 * Setup the add/edit form
	 */
	private function fillForm($data) {
		$this->assign('js', '/apps/user/admin/js/catChange.js');
		$this->assign('groups', $this->model->getGroupsList());
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
		
		$model = array(
			'id' => 0,
			'nickname' => '', 
			'email' => '',
			'groupe' => 0,
			'access' => array()
		);
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function add($data = array()) {
		$this->fillForm($data);
	}
	
	public function edit($userid) {
		$this->fillForm($this->model->getUser($userid));
	}
	
	public function del($userid) {
		$data = $this->model->getUser($userid);
		$this->assign('nickname', $data['nickname']);
	}
	
	public function groups_listing($sortBy, $sens) {
		$this->assign('js', '/apps/user/admin/js/cat.js');
		$this->assign('css', '/apps/user/admin/css/user.css');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminApps());
		
		// AdminStyle Helper
		$dispFields = array('name');
		$adminStyle = WHelper::load('SortingHelper', array($dispFields, 'name'));
		$sort = $adminStyle->getSorting($sortBy, $sens); // sorting vars
		
		// Enregistrement des variables de classement
		$this->assign($adminStyle->getTplVars());
		
		$data = $this->model->getGroupsList($sort[0], $sort[1] == 'ASC');
		$this->assign('cats', $data);
	}
}

?>