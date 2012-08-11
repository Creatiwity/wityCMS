<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * @author Fofif
 * @version	$Id: apps/news/admin/view.php 0002 01-08-2011 Fofif $
 */

class NewsAdminView extends WView {
	private $model;
	
	public function __construct(NewsAdminModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	public function index($sortBy, $sens) {
		// AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$dispFields = array('id', 'title', 'author', 'date', 'modified');
		$adminStyle = new AdminStyle($dispFields, 'date', 'DESC');
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$data = $this->model->getNewsList(0, 20, $sort[0], $sort[1] == 'ASC');
		foreach ($data as $values) {
			$this->tpl->assignBlockVars('news', $values);
		}
	}
	
	/**
	 * Fonction de chargement de la page principale du formulaire de news
	 */
	private function loadMainForm() {
		// JS / CSS
		$this->assign('js', '/apps/news/admin/js/add.js');
		$this->assign('js', '/helpers/ckeditor/ckeditor.js');
		$this->assign('js', '/helpers/ckfinder/ckfinder.js');
		
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', $_SERVER['SERVER_NAME'].WRoute::getDir().'news/');
		
		// Chargement des catégories
		$data = $this->model->getCatList("name", "ASC");
		foreach ($data as $values) {
			$this->tpl->assignBlockVars('cat', $values);
		}
	}
	
	/**
	 * Définition des valeurs de contenu du formulaire
	 */
	private function fillMainForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function add($data = array()) {
		$this->assign('css', '/apps/news/admin/css/add.css');
		$this->loadMainForm();
		
		// Id pour simuler le permalien
		$this->assign('lastId', $this->model->getLastNewsId()+1);
		
		$this->fillMainForm(
			array(
				'nAuthor' => $_SESSION['nickname'],
				'nKeywords' => '',
				'nTitle' => "Le titre de votre article",
				'nTitleClass' => 'empty',
				'nUrl' => '',
				'nContent' => ''
			),
			$data
		);
	}
	
	public function edit($id, $formData = array()) {
		$this->assign('css', '/apps/news/admin/css/edit.css');
		$this->loadMainForm();
		
		// Chargement des données
		$data = $this->model->loadNews($id);
		
		$this->assign('lastId', $id);
		$this->assign('date', $data['date']);
		$this->assign('modified', $data['modified']);
		$this->assign('image', $data['image']);
		$ids = array();
		foreach ($this->model->findNewsCats($id) as $row) {
			$ids[] = $row['cat_id'];
		}
		$this->assign('ncat', $ids);
		
		$this->fillMainForm(array(
				'nAuthor' => $data['author'],
				'nKeywords' => $data['keywords'],
				'nTitle' => $data['title'],
				'nTitleClass' => '',
				'nUrl' => $data['url'],
				'nContent' => $data['content']
			), 
			$formData
		);
	}
	
	public function del($id) {
		$data = $this->model->loadNews($id);
		$this->assign('nTitle', $data['title']);
	}
	
	public function cat($sortBy, $sens) {
		$this->assign('css', '/apps/news/admin/css/cat.css');
		$this->assign('js', '/apps/news/admin/js/cat.js');
		
		// AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$dispFields = array('name', 'shortname');
		$adminStyle = new AdminStyle($dispFields, 'name');
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$data = $this->model->getCatList($sort[0], $sort[1] == 'ASC');
		foreach ($data as $values) {
			$this->tpl->assignBlockVars('cat', $values);
		}
	}
}

?>
