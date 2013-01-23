<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 */

class PageAdminView extends WView {
	private $model;
	
	public function __construct(PageAdminModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	public function liste($sortBy, $sens) {
		// sortingHelper Helper
		$dispFields = array('id', 'title', 'author', 'creation_time', 'edit_time');
		$sortingHelper = WHelper::load('SortingHelper', array($dispFields, 'title'));
		
		// Sorting vars
		$sort = $sortingHelper->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->assign($sortingHelper->getTplVars());
		
		$data = $this->model->getPageList(0, 20, $sort[0], $sort[1] == 'ASC');
		$this->assign('pages', $data);
	}
	
	public function add($data = array()) {
		// JS / CSS
		$this->assign('css', '/apps/page/admin/css/add.css');
		$this->assign('js', '/libraries/ckeditor/ckeditor.js');
		$this->assign('js', '/libraries/ckfinder/ckfinder.js');
		$this->assign('js', '/apps/page/admin/js/add.js');
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', WRoute::getBase());
		
		/**
		 * VALEURS DE CONTENU
		 */
		if (isset($data['pAuthor'])) {
			$this->assign('pAuthor', $data['pAuthor']);
		} else {
			$this->assign('pAuthor', $_SESSION['nickname']);
		}
		
		if (isset($data['pKeywords'])) {
			$this->assign('pKeywords', $data['pKeywords']);
		} else {
			$this->assign('pKeywords', '');
		}
		
		if (isset($data['pTitle'])) {
			$this->assign('pTitle', $data['pTitle']);
		} else {
			$this->assign('pTitle', '');
		}
		
		if (isset($data['pUrl'])) {
			$this->assign('pUrl', $data['pUrl']);
		} else {
			$this->assign('pUrl', '');
		}
		
		if (isset($data['pContent'])) {
			$this->assign('pContent', $data['pContent']);
		} else {
			$this->assign('pContent', '');
		}
		/**
		 * FIN VALEURS DE CONTENU
		 */
	}
	
	public function edit($id, $formData = array()) {
		$this->assign('css', '/apps/page/admin/css/edit.css');
		$this->assign('js', '/libraries/ckeditor/ckeditor.js');
		$this->assign('js', '/libraries/ckfinder/ckfinder.js');
		$this->assign('js', '/apps/page/admin/js/add.js');
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', WRoute::getBase());
		
		$data = $this->model->loadPage($id);
		
		$this->assign('creation_time', "le ".date('d/m/Y à H:i', intval($data['creation_time'])));
		$this->assign('edit_time', "le ".date('d/m/Y à H:i', intval($data['edit_time'])));
		
		/**
		 * VALEURS DE CONTENU
		 */
		if (!empty($formData['pAuthor'])) {
			$this->assign('pAuthor', $formData['pAuthor']);
		} else {
			$this->assign('pAuthor', $data['author']);
		}
		
		if (!empty($formData['pKeywords'])) {
			$this->assign('pKeywords', $formData['pKeywords']);
		} else {
			$this->assign('pKeywords', $data['keywords']);
		}
		
		if (!empty($formData['pTitle'])) {
			$this->assign('pTitle', $formData['pTitle']);
		} else {
			$this->assign('pTitle', $data['title']);
		}
		
		if (!empty($formData['pUrl'])) {
			$this->assign('pUrl', $formData['pUrl']);
		} else {
			$this->assign('pUrl', $data['url']);
		}
		
		if (!empty($formData['pContent'])) {
			$this->assign('pContent', $formData['pContent']);
		} else {
			$this->assign('pContent', $data['content']);
		}
		/**
		 * FIN VALEURS DE CONTENU
		 */
	}
	
	public function del($id) {
		$data = $this->model->loadPage($id);
		$this->assign('pTitle', $data['title']);
	}
}

?>