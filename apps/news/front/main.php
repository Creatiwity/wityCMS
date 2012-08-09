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
		$action = $this->getAskedAction();
		$this->forward($action, 'index');
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
	
	protected function index() {
		$id = $this->getId();
		// Si l'id fourni est valide, on charge la news demandée
		if (!empty($id) && $this->model->validId($id)) {
			$this->view->detail($id);
			$this->render('detail');
		} else {
			$this->view->index();
			$this->render('news');
		}
	}
}

?>