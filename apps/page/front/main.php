<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: main.php 0001 10-04-2011 Fofif $
 */

class PageController extends WController {
	protected $actionList = array(
		'index' => "Lecture d'une page",
	);
	
	/*
	 * Chargement du modèle et de la view
	 */
	public function __construct() {
		include 'model.php';
		$this->model = new PageModel();
		
		include 'view.php';
		$this->setView(new PageView($this->model));
	}
	
	/**
	 * Récupère un id fourni dans l'url
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[0])) {
			return 0;
		} else {
			list($id) = explode('-', $args[0]);
			return (!$this->model->validId($id)) ? 0 : intval($id);
		}
	}
	
	public function launch() {
		$action = $this->getAskedAction();
		$this->forward($action, 'index');
	}
	
	protected function index() {
		$id = $this->getId();
		if (!empty($id)) {
			$this->view->see($id);
			$this->render('see');
		} else {
			WNote::error("Page inexistante", "La page que vous avez demandée n'existe pas", 'display');
		}
	}
}

?>