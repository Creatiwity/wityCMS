<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Copyright 2010 NkDeuS.Com
 */

class PageAdminController extends WController {
	public function __construct() {
		// Chargement des modèles
		include 'model.php';
		$this->model = new PageAdminModel();
		
		include 'view.php';
		$this->setView(new PageAdminView($this->model));
	}
	
	protected function liste() {
		$args = WRoute::getArgs();
		$sortData = explode('-', array_shift($args));
		if (empty($sortData)) {
			$sortBy = '';
			$sens = '';
		} else {
			$sortBy = array_shift($sortData);
			$sens = !empty($sortData) ? $sortData[0] : '';
		}
		
		$this->view->liste($sortBy, $sens);
		$this->view->render('liste');
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
				WNote::error('data_errors', implode("<br />\n", $erreurs));
				$this->view->add($data);
				$this->vieww->render('add');
			} else {
				// Mise à jour des infos
				if ($this->model->createPage($data)) {
					// Mettre à jour le routage custom
					WRoute::defineCustomRoute($data['pUrl'], array('page', array($this->model->getLastId())));
					
					WNote::success('page_created', "La page <strong>".$data['pTitle']."</strong> a été créée avec succès.");
					header('location: '.WRoute::getDir().'/admin/page/');
				} else {
					WNote::success('page_not_created', "Un problème d'origine inconnue est survenu lors de la création de la page <strong>".$data['pTitle']."</strong>");
					$this->view->add($data);
					$this->view->render('add');
				}
			}
		} else {
			$this->view->add();
			$this->view->render('add');
		}
	}
	
	protected function edit() {
		$id = $this->getId();
		
		// Vérification de la validité de l'id
		if (!$this->model->validId($id)) {
			WNote::error('page_not_found', "La page que vous tentez de modifier n'existe pas.");
			header('location: '.WRoute::getDir().'/admin/page/');
			return;
		}
		
		$data = WRequest::getAssoc(array('pAuthor', 'pKeywords', 'pTitle', 'pUrl', 'pContent'));
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
				WNote::error('data_errors', implode("<br />\n", $erreurs), 'assign');
				$this->view->edit($id, $data);
				$this->render('edit');
			} else {
				// Mise à jour des infos
				if ($this->model->updatePage($id, $data)) {
					// Mettre à jour le routage custom
					// Pour l'édition, on doit supprimer l'ancien routage et ajouter le niveau
					if ($realData['url'] != $data['pUrl']) {
						WRoute::defineCustomRoute($data['pUrl'], array('page', array($id)));
					}
					
					WNote::success('page_edited', "La page <strong>".$data['pTitle']."</strong> a été modifiée avec succès.");
					header('location: '.WRoute::getDir().'/admin/page/');
				} else {
					WNote::success('page_not_edited', "Un problème d'origine inconnue est survenu lors de l'édition de la page <strong>".$data['pTitle']."</strong>");
					$this->view->edit($id, $data);
					$this->view->render('edit');
				}
			}
		} else {
			$this->view->edit($id);
			$this->view->render('edit');
		}
	}
	
	protected function del() {
		$id = $this->getId();
		if ($this->model->validId($id)) {
			if (WRequest::get('confirm', null, 'POST') === '1') {
				$data = $this->model->loadPage($id);
				
				// Delete from DB
				$this->model->deletePage($id);
				
				// Delete custom route
				WRoute::deleteCustomRoute($data['url']);
				
				WNote::success('page_deleted', 'La page "<strong>'.$data['title'].'</strong>" a été supprimée avec succès.');
				header('location: '.WRoute::getDir().'/admin/page/');
			} else {
				$this->view->del($id);
				$this->view->render('del');
			}
		} else {
			WNote::error('page_not_found', "La page que vous tentez de supprimer n'existe pas.");
			header('location: '.WRoute::getDir().'/admin/page/');
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