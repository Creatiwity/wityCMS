<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: view.php 0001 10-04-2011 Fofif $
 */

class PageView extends WView {
	private $model;
	
	public function __construct(PageModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	public function see($id) {
		$data = $this->model->loadPage($id);
		$this->assign('pageTitle', WConfig::get('config.siteName')." | ".$data['title']);
		$this->assign($data);
	}
}

?>