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
		include HELPERS_DIR.'SortingHelper'.DS.'SortingHelper.php';
		$dispFields = array('id', 'title', 'author', 'cat', 'date', 'views');
		$adminStyle = new SortingHelper($dispFields, 'date', 'DESC');
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->assign($adminStyle->getTplVars());
		
		$data = $this->model->getNewsList(0, 20, $sort[0], $sort[1] == 'ASC');
		$this->assign('news', $data);
		$this->setResponse('index');
	}
	
	/**
	 * Fonction de chargement de la page principale du formulaire de news
	 */
	private function loadMainForm() {
		// JS / CSS
		$this->assign('js', '/apps/news/admin/js/add.js');
		
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', WRoute::getBase().'/news/');
		
		// Chargement des catégories
		$data = $this->model->getCatList("name", "ASC");
		$this->assign('cat', $data);
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
		$this->loadMainForm();
		
		// Id pour simuler le permalien
		$this->assign('lastId', $this->model->getLastNewsId()+1);
                
                $this->assign('css', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.css");
                $this->assign('js', "/libraries/wysihtml5-bootstrap/wysihtml5.min.js");
                $this->assign('js',"/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.min.js");
		
		$ids = array();
		if(!empty($data)) {
			foreach ($data['nCat'] as $row => $val) {
				$ids[] = $row;
			}
		}
		$this->assign('ncat', $ids);
		
		$this->fillMainForm(
			array(
				'nAuthor' => $_SESSION['nickname'],
				'nKeywords' => '',
				'nTitle' => '',
				'nTitleClass' => 'empty',
				'nUrl' => '',
				'nContent' => '',
			),
			$data
		);
		
		$this->setResponse('add');
		$this->render();
	}
	
	public function edit($id, $formData = array()) {
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
		
		$this->setResponse('add');
		$this->render();
	}
	
	public function del($id) {
		$data = $this->model->loadNews($id);
		$this->assign('nTitle', $data['title']);
		
		$this->setResponse('del');
	}
	
	public function cat($sortBy, $sens) {
		$this->assign('css', '/apps/news/admin/css/cat.css');
		$this->assign('js', '/apps/news/admin/js/cat.js');
		
		// AdminStyle Helper
		$orderingFields = array('name', 'shortname');
		$adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'name'));
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$data = $this->model->getCatList($sort[0], $sort[1] == 'ASC');
		$this->assign('cat', $data);
		
		$this->setResponse('cat');
	}
}

?>
