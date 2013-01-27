<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: apps/user/admin/view.php 0004 02-02-2012 Fofif $
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
	public function liste($sortBy, $sens, $currentPage, $filtres) {
		$n = 40; // 40 users per page
		
		// SortingHelper Helper
		$sortingHelper = WHelper::load('SortingHelper', array(array('id', 'nickname', 'email', 'date', 'groupe', 'last_activity'), 'date', 'DESC'));
		$sort = $sortingHelper->getSorting($sortBy, $sens);
		
		// Register sorting vars to be displayed in the head of the sorting table
		$this->assign($sortingHelper->getTplVars());
		
		// Treat filters
		$subURL = "";
		foreach ($filtres as $k => $v) {
			// Cleanup filters
			if (!empty($v)) {
				$subURL .= $k."=".$v."&";
			}
		}
		if (!empty($subURL)) {
			$subURL = '?'.substr($subURL, 0, -1);
		}
		$this->assign('subURL', $subURL);
		$this->assign($filtres);
		
		// Get the user groups
		$this->assign('groups', $this->model->getCatList());
		
		// Assign main data
		$data = $this->model->getUserList(($currentPage-1)*$n, $n, $sort[0], $sort[1] == 'ASC', $filtres);
		$this->assign('users', $data);
		
		// Generate the pagination to navigate data
		$count = $this->model->countUsersWithFilters($filtres);
		$pagination = WHelper::load('pagination', array($count, $n, $currentPage, '/admin/user/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'.$subURL));
		$this->assign('pagination', $pagination->getHtml());
		$this->assign('total', $count);
	}
	
	/**
	 * Définition des valeurs de contenu du formulaire
	 */
	private function fillForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function add($data = array()) {
		$this->assign('js', '/apps/user/admin/js/catChange.js');
		$this->assign('cats', $this->model->getCatList());
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminAppList());
		
		$this->fillForm(
			array(
				'nickname' => '', 
				'email' => '',
				'access' => array(),
			),
			$data
		);
	}
	
	public function edit($id) {
		$this->assign('userid', $id);
		$this->assign('cats', $this->model->getCatList());
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminAppList());
		
		$data = $this->model->getUserData($id);
		$data['accessArray'] = explode(',', $data['access']);
		$this->assign($data);
	}
	
	public function del($id) {
		$data = $this->model->getUserData($id);
		$this->assign('nickname', $data['nickname']);
	}
	
	public function cat($sortBy, $sens) {
		$this->assign('js', '/apps/user/admin/js/cat.js');
		$this->assign('css', '/apps/user/admin/css/user.css');
		
		// Get admin apps
		$adminModel = new AdminController();
		$this->assign('admin_apps', $adminModel->getAdminAppList());
		
		// AdminStyle Helper
		$dispFields = array('name');
		$adminStyle = WHelper::load('SortingHelper', array($dispFields, 'name'));
		$sort = $adminStyle->getSorting($sortBy, $sens); // sorting vars
		
		// Enregistrement des variables de classement
		$this->assign($adminStyle->getTplVars());
		
		$data = $this->model->getCatList($sort[0], $sort[1] == 'ASC');
		$this->assign('cats', $data);
	}
}

?>