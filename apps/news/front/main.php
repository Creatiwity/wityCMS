<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: main.php 0001 09-04-2011 Fofif $
 */

class NewsController extends WController {
	protected $actionList = array(
		'index' => "Liste des articles",
	);
	
	/*
	 * Chargement du modèle et de la view
	 */
	public function __construct() {
		include 'model.php';
		$this->model = new NewsModel();
		
		include 'view.php';
		$this->setView(new NewsView($this->model));
	}
	
	public function launch() {
		$newsid = $this->getId();
		if (!empty($newsid) && $this->model->validId($newsid)) {
			$this->displayItem($newsid);
		} else {
			$this->listNews();
		}
	}
	
	/**
	 * Récupère un id fourni dans l'url
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[0])) {
			return -1;
		} else {
			list($id) = explode('-', $args[0]);
			return intval($id);
		}
	}
	
	protected function listNews() {
		$this->view->listing();
		$this->render('listing');
	}
	
	protected function displayItem($id) {
		$this->view->detail($id);
		$this->render('detail');
	}
}

?>