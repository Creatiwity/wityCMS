<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Copyright 2010 NkDeuS.Com
 */

class PageAdminController extends WController {
	protected $actionList = array(
		'index' => "Liste des pages",
		'add' => "Ajouter une page",
		'edit' => "\Edition d'une page",
		'del' => "\Suppression d'une page"
	);
	
	public function __construct() {
		// Chargement des modèles
		include 'model.php';
		$this->model = new PageAdminModel();
		
		include 'view.php';
		$this->setView(new PageAdminView($this->model));
	}
	
	public function launch() {
		$action = $this->getAskedAction();
		$this->forward($action, 'index');
	}
	
	protected function index() {
		$args = WRoute::getArgs();
		$sortData = explode('-', array_shift($args));
		if (empty($sortData)) {
			$sortBy = '';
			$sens = '';
		} else {
			$sortBy = array_shift($sortData);
			$sens = !empty($sortData) ? $sortData[0] : '';
		}
		
		// Les notes
		WNote::treatNoteSession();
		
		$this->view->index($sortBy, $sens);
		$this->render('index');
	}
	
	protected function add() {
		$data = WRequest::get(array('pAuthor', 'pKeywords', 'pTitle', 'pUrl', 'pContent'), null, 'POST', false);
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$erreurs = array();
			
			/**
			 * VERIFICATIONS
			 */
			if (empty($data['pTitle'])) {
				$erreurs[] = "Il manque un titre à la page.";
			}
			
			if (empty($data['pAuthor'])) {
				$erreurs[] = "Il manque un auteur à la page.";
			}
			
			// Traitement du permalien pour assurer
			$data['pUrl'] = strtolower($data['pUrl']);
			$data['pUrl'] = preg_replace('#[^a-z0-9.]#', '-', $data['pUrl']);
			$data['pUrl'] = preg_replace('#-{2,}#', '-', $data['pUrl']);
			$data['pUrl'] = preg_replace('#(^-|-$)#i', '', $data['pUrl']);
			if (!$this->model->permalienAvailable($data['pUrl'])) {
				$erreurs[] = "Il existe déjà une page avec ce permalien. Veuillez en spécifier un autre.";
			}
			/**
			 * FIN VERIFICATIONS
			 */
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
				$this->view->add($data);
				$this->render('add');
			} else {
				// Mise à jour des infos
				if ($this->model->createPage($data)) {
					// Mettre à jour le routage perso
					WRoute::defineRoutePerso($data['pUrl'], array('page', array($this->model->getLastId())));
					
					WNote::success("Page créée", "La page <strong>".$data['pTitle']."</strong> a été créée avec succès.", 'session');
					header('location: '.WRoute::getDir().'admin/page/');
				} else {
					echo 'Error';
				}
			}
		} else {
			$this->view->add();
			$this->render('add');
		}
	}
	
	protected function edit() {
		$id = $this->getId();
		
		// Vérification de la validité de l'id
		if (!$this->model->validId($id)) {
			WNote::error("Page introuvable", "La page que vous tentez de modifier n'existe pas.", 'session');
			header('location: '.WRoute::getDir().'admin/page/');
			return;
		}
		
		$data = WRequest::get(array('pAuthor', 'pKeywords', 'pTitle', 'pUrl', 'pContent'), null, 'POST', false);
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$erreurs = array();
			// Récupération des données liées à cette page pour comparaison
			$realData = $this->model->loadPage($id);
			
			/**
			 * VERIFICATIONS
			 */
			if (empty($data['pTitle'])) {
				$erreurs[] = "Il manque un titre à la page.";
			}
			
			if (empty($data['pAuthor'])) {
				$erreurs[] = "Il manque un auteur à la page.";
			}
			
			if (empty($data['pUrl'])) {
				$erreurs[] = "Aucun permalien (lien vers la page) n'a été défini.";
			} else {
				// Traitement du permalien pour assurer
				$data['pUrl'] = strtolower($data['pUrl']);
				$data['pUrl'] = preg_replace('#[^a-z0-9.]#', '-', $data['pUrl']);
				$data['pUrl'] = preg_replace('#-{2,}#', '-', $data['pUrl']);
				$data['pUrl'] = preg_replace('#(^-|-$)#i', '', $data['pUrl']);
				// On vérifie bien qu'il y a eu modification du permalien
				if ($realData['url'] != $data['pUrl'] && !$this->model->permalienAvailable($data['pUrl'])) {
					$erreurs[] = "Il existe déjà une page avec ce permalien. Veuillez en spécifier un autre.";
				}
			}
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error("Informations invalides", implode("<br />\n", $erreurs), 'assign');
				$this->view->edit($id, $data);
				$this->render('edit');
			} else {
				// Mise à jour des infos
				if ($this->model->updatePage($id, $data)) {
					// Mettre à jour le routage perso
					// Pour l'édition, on doit supprimer l'ancien routage et ajouter le niveau
					if ($realData['url'] != $data['pUrl']) {
						WRoute::deleteRoutePerso($realData['url']);
						WRoute::defineRoutePerso($data['pUrl'], array('page', array($id)));
					}
					
					WNote::success("Page éditée", "La page <strong>".$data['pTitle']."</strong> a été modifiée avec succès.", 'session');
					header('location: '.WRoute::getDir().'admin/page/');
				} else {
					echo 'Error';
				}
			}
		} else {
			$this->view->edit($id);
			$this->render('edit');
		}
	}
	
	protected function del() {
		$id = $this->getId();
		if ($this->model->validId($id)) {
			if (WRequest::get('confirm', null, 'POST') === '1') {
				$data = $this->model->loadPage($id);
				$this->model->deletePage($id);
				WRoute::deleteRoutePerso($data['url']);
				WNote::success("Suppression d'une page", "La page \"<strong>".$data['title']."</strong>\" a été supprimée avec succès.", 'session');
				header('location: '.WRoute::getDir().'admin/page/');
			} else {
				$this->view->del($id);
				$this->render('del');
			}
		} else {
			WNote::error("Page introuvable", "La page que vous tentez de supprimer n'existe pas.", 'session');
			header('location: '.WRoute::getDir().'admin/page/');
		}
	}
	
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[1])) {
			return -1;
		} else {
			list ($id) = explode('-', $args[1]);
			return intval($id);
		}
	}
}

?>