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
	
	public function liste($sortBy, $sens, $currentPage, $filtres) {
		// -- AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$adminStyle = new AdminStyle(array('id', 'nickname', 'email', 'date', 'groupe', 'last_activity'), 'date', 'DESC');
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		// Traitement des filtres
		$subURL = "";
		foreach ($filtres as $k => $v) {
			// Nettoyage des filtres
			if (!empty($v)) {
				$subURL .= $k."=".$v."&";
			}
		}
		if (!empty($subURL)) {
			$subURL = '?'.substr($subURL, 0, -1);
		}
		$this->tpl->assign('subURL', $subURL);
		$this->tpl->assign($filtres);
		
		// -- Récupération des groupes
		$groups = $this->model->getCatList();
		foreach ($groups as $g) {
			$this->tpl->assignBlockVars('groups', $g);
		}
		
		$n = 40; // 40 utilisateurs par page
		$data = $this->model->getUserList(($currentPage-1)*$n, $n, $sort[0], $sort[1] == 'ASC', $filtres);
		foreach ($data as $values) {
			// if ($values['factivity'] == '00/00/0000 00:00') {
				// $values['factivity'] = 'N/A';
			// }
			$values['access'] = explode(',', $values['access']);
			$this->tpl->assignBlockVars('users', $values);
		}
		
		// -- Génération de la numérotation
		$count = $this->model->countUsersWithFilters($filtres);
		include HELPERS_DIR.'pagination'.DS.'pagination.php';
		$page = new Pagination($count, $n, $currentPage, '/admin/user/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'.$subURL);
		$this->assign('pagination', $page->getHtml());
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
		
		$adminModel = new AdminModel();
		foreach($adminModel->getAdminAppList() as $mod) {
			$levels = $adminModel->getLevels($mod); // level = array('id' => 0, 'title' => 'nom du niveau')
			$this->tpl->assignBlockVars('module', array(
				'name' => $mod,
				'levels' => $levels
			));
		}
		
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
		$this->assign('cats', $this->model->getCatList());
		
		$adminModel = new AdminModel();
		foreach($adminModel->getAdminAppList() as $mod) {
			$levels = $adminModel->getLevels($mod); // level = array('id' => 0, 'title' => 'nom du niveau')
			$this->tpl->assignBlockVars('module', array(
				'name' => $mod,
				'levels' => $levels
			));
		}
		
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
		
		$adminModel = new AdminModel();
		foreach($adminModel->getAdminAppList() as $mod) {
			$levels = $adminModel->getLevels($mod); // level = array('id' => 0, 'title' => 'nom du niveau')
			$this->tpl->assignBlockVars('module', array(
				'name' => $mod,
				'levels' => $levels
			));
		}
		
		// AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$dispFields = array('name');
		$adminStyle = new AdminStyle($dispFields, 'name');
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$data = $this->model->getCatList($sort[0], $sort[1] == 'ASC');
		foreach ($data as $values) {
			//$values['access'] = explode(',', $values['access']);
			$this->tpl->assignBlockVars('cat', $values);
		}
	}
}

?>