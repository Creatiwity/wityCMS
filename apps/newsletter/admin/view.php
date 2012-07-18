<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/newsletter/admin/view.php 0001 06-10-2011 Fofif $
 */

class NewsletterAdminView extends WView {
	private $model;
	
	public function __construct(NewsletterAdminModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	/**
	 * Liste des entreprises
	 */
	public function liste($sortBy, $sens, $currentPage) {
		// AdminStyle Helper
		include HELPERS_DIR.'adminStyle'.DS.'adminStyle.php';
		$adminStyle = new AdminStyle(array('objet', 'date'), 'date', 'DESC');
		// Sorting vars
		$sort = $adminStyle->getSorting($sortBy, $sens);
		// Enregistrement des variables de classement
		$this->tpl->assign($adminStyle->getTplVars());
		
		$count = $this->model->countNewsletters();
		
		// Gestion de la pagination (50 firms/page)
		include HELPERS_DIR.'pagination'.DS.'pagination.php';
		$page = new Pagination($count, 50, $currentPage, '/admin/newsletters/'.$sort[0].'-'.strtolower($sort[1]).'-%d');
		$this->assign('pagination', $page->getHtml());
		
		$newsletters = $this->model->getNewsletters(($currentPage-1)*50, 50, $sort[0], $sort[1] == 'ASC');
		foreach ($newsletters as $n) {
			$n['dest_count'] = substr_count($n['destinataires'], ';');
			$this->tpl->assignBlockVars('newsletter', $n);
		}
	}
	
	public function read($nid) {
		$this->assign('css', '/apps/newsletter/admin/css/write.css');
		
		$data = $this->model->loadNewsletter($nid);
		
		$data['destinataires'] = explode('; ', $data['destinataires']);
		array_pop($data['destinataires']);
		$data['dest_count'] = count($data['destinataires']);
		
		$data['attachment'] = basename($data['attachment']);
		
		$this->assign($data);
	}
	
	/**
	 * Fonction de chargement de la page principale du formulaire de news
	 */
	private function loadMainForm() {
		// JS / CSS
		$this->assign('js', '/helpers/ckeditor/ckeditor.js');
		$this->assign('js', '/helpers/ckfinder/ckfinder.js');
		
		$this->assign('baseDir', WRoute::getDir());
	}
	
	/**
	 * Définition des valeurs de contenu du formulaire
	 */
	private function fillMainForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function write(array $data = array()) {
		$this->assign('css', '/apps/newsletter/admin/css/write.css');
		$this->assign('js', '/apps/newsletter/admin/js/write.js');
		$this->loadMainForm();
		
		$this->fillMainForm(
			array(
				'de' => "forum@mines.inpl-nancy.fr",
				'objet' => "Objet",
				'message' => ''
			),
			$data
		);
	}
}

?>
