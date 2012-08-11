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
	
	public function index($sortBy, $sens) {
		// AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$dispFields = array('id', 'title', 'author', 'creation_time', 'edit_time');
		$adminStyle = new AdminStyle($dispFields, 'title');
		
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$data = $this->model->getPageList(0, 20, $sort[0], $sort[1] == 'ASC');
		foreach ($data as $values) {
			$values['creation_time'] = "le ".date('d/m/Y à H:i', intval($values['creation_time']));
			$values['edit_time'] = "le ".date('d/m/Y à H:i', intval($values['edit_time']));
			$this->tpl->assignBlockVars('pages', $values);
		}
	}
	
	public function add($data = array()) {
		// JS / CSS
		$this->assign('css', '/apps/page/admin/css/add.css');
		$this->assign('js', '/helpers/ckeditor/ckeditor.js');
		$this->assign('js', '/helpers/ckfinder/ckfinder.js');
		$this->assign('js', '/apps/page/admin/js/add.js');
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', $_SERVER['SERVER_NAME'].WRoute::getDir());
		
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
		$this->assign('js', '/helpers/ckeditor/ckeditor.js');
		$this->assign('js', '/helpers/ckfinder/ckfinder.js');
		$this->assign('js', '/apps/page/admin/js/add.js');
		$this->assign('baseDir', WRoute::getDir());
		
		// Assignation de l'adresse du site pour le permalien
		$this->assign('siteURL', $_SERVER['SERVER_NAME'].WRoute::getDir());
		
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