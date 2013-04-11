<?php
/**
 * News Application - Admin Controller - /apps/news/admin/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminController is the Admin Controller of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-11-04-2013
 */
class NewsAdminController extends WController {
	public function __construct() {
		include 'model.php';
		$this->model = new NewsAdminModel();
		
		include 'view.php';
		$this->setView(new NewsAdminView());
	}
	
	/**
	 * Récupère un id fourni dans l'url
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[1])) {
			return -1;
		} else {
			list ($id) = explode('-', $args[1]);
			return intval($id);
		}
	}
	
	protected function listing() {
		$n = 30; // number of news per page
		
		// Sorting criterias given by URL
		$args = WRoute::getArgs();
		$criterias = array_shift($args);
		if ($criterias == 'listing') {
			$criterias = array_shift($args);
		}
		$count = sscanf(str_replace('-', ' ', $criterias), '%s %s %d', $sortBy, $sens, $page);
		if (!isset($this->model->news_data_model['toDB'][$sortBy])) {
			$sortBy = 'news_date';
		}
		if (empty($page) || $page <= 0) {
			$page = 1;
		}
		
		// AdminStyle Helper
		$orderingFields = array('news_id', 'news_title', 'news_author', 'news_date', 'news_views');
		$adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'news_date', 'DESC'));
		$sorting = $adminStyle->findSorting($sortBy, $sens);
		
		// Get data
		$news = $this->model->getNewsList(($page-1)*$n, $n, $sorting[0], $sorting[1] == 'ASC');
		$total = $this->model->countNews();
		
		// Pagination
		$pagination = WHelper::load('pagination', array($total, $n, $page, '/admin/news/'.$sorting[0].'-'.$sorting[1].'-%d/'));
		
		$this->view->news_listing($news, $adminStyle, $pagination);
	}
	
	/*
	 * Ajout d'une news
	 */
	protected function news_add_or_edit() {
		$data = WRequest::getAssoc(array('news_author', 'news_keywords', 'news_title', 'news_url', 'news_content'));
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$erreurs = array();
			
			/**
			 * VERIFICATIONS
			 */
			if (empty($data['news_title'])) {
				$erreurs[] = WLang::get("article_no_title");
			}
			
			if (empty($data['news_author'])) {
				$erreurs[] = WLang::get("article_no_author");
			}
			
			// Traitement du permalien
			if (empty($data['news_url'])) {
				$erreurs[] = WLang::get("article_no_permalink");
			} else {
				$data['news_url'] = strtolower($data['news_url']);
				$data['news_url'] = preg_replace('#[^a-z0-9.]#', '-', $data['news_url']);
				$data['news_url'] = preg_replace('#-{2,}#', '-', $data['news_url']);
				$data['news_url'] = trim($data['news_url'], '-');
			}
			
			/**
			 * FIN VERIFICATIONS
			 */
			// Categories
			$data['news_cats'] = WRequest::get('news_cats');
			
			// Traitement l'image à la une
			if (!empty($_FILES['news_image']['name'])) {
				include HELPERS_DIR . 'upload/upload.php';
				$upload = new Upload($_FILES['news_image']);
				$upload->file_new_name_body = preg_replace('#[^a-z0-9]#', '', strtolower($data['news_title']));
				$upload->file_overwrite = true;
				$upload->Process(WT_PATH . 'upload/news/');
				if (!$upload->processed) {
					$erreurs[] = WLang::get("article_image_error", $upload->error);
				}
				$data['news_image'] = $upload->file_dst_name;
			} else {
				$data['news_image'] = '';
			}
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error('data_errors', implode("<br />\n", $erreurs), 'assign');
				$catList = $this->model->getCatList("name", "ASC");
				$lastId = $this->getId();
				
				// Vérification de la validité de l'id
				if (!$this->model->validExistingNewsId($lastId)) {
					$lastId = $this->model->getLastNewsId() + 1;
				}
				$this->view->news_add_or_edit($catList, $lastId, $data);
			} else {
				$id = $this->getId();
				
				// Vérification de la validité de l'id
				if (!$this->model->validExistingNewsId($id)) {
					// Mise à jour des infos
					if ($this->model->createNews($data)) {
						// Traitement des catégories
						if (!empty($data['news_cats'])) {
							$nid = $this->model->getLastNewsId();
							// Récupération des id des catégories stockées dans les clés du tableau
							foreach ($data['news_cats'] as $cid => $v) {
								$this->model->newsAddCat($nid, intval($cid));
							}
						}
						
						WNote::success('article_added', WLang::get('article_added', $data['news_title']));
						header('location: ' . WRoute::getDir() . '/admin/news/');
					} else {
						WNote::error('article_not_added', WLang::get('article_not_added'));
						$catList = $this->model->getCatList("name", "ASC");
						$lastId = $this->model->getLastNewsId() + 1;
						$this->view->news_add_or_edit($catList, $lastId, $data);
					}
				} else {
					$data['news_id'] = $id;
					
					// Traitement des catégories
					$this->model->newsDestroyCats($id);
					if (!empty($data['news_cats'])) {
						// Récupération des id des catégories stockées dans les clés du tableau
						foreach ($data['news_cats'] as $cid => $v) {
							$this->model->newsAddCat($id, intval($cid));
						}
					}
					
					// Ajout du nom de l'éditeur
					$data['news_editor_id'] = $_SESSION['nickname'];
					
					// Mise à jour des infos
					if ($this->model->updateNews($data)) {
						WNote::success('article_edited', WLang::get('article_edited', $data['news_title']));
						header('location: ' . WRoute::getDir() . '/admin/news/');
					} else {
						WNote::error('article_not_edited', WLang::get('article_image_error'));
						$catList = $this->model->getCatList("name", "ASC");
						$this->view->news_add_or_edit($catList, $id, $data);
					}
				}
			}
		} else {
			$id = $this->getId();
			$catList = $this->model->getCatList("name", "ASC");
			
			if ($this->model->validExistingNewsId($id)) {
				$data = $this->model->loadNews($id);
				$cats = $this->model->findNewsCats($id);
				foreach ($cats as $key => $cat_id) {
					$data['news_cats'][strval($cat_id['cat_id'])] = 'on';
				}
				$this->view->news_add_or_edit($catList, $id, $data);
			} else {
				$lastId = $this->model->getLastNewsId() + 1;
				$this->view->news_add_or_edit($catList, $lastId);
			}
		}
	}
	
	protected function news_delete() {
		$args = WRoute::getArgs();
		$id = -1;
		$confirm = false;
		
		if(!empty($args[1])) {
			$args = explode("-",$args[1]);
			$id = $args[0];
			
			if(!empty($args[1]) && $args[1] == "confirm") {
				$confirm = true;
			}
		}
		
		if ($this->model->validExistingNewsId($id)) {
			$data = $this->model->loadNews($id);
			
			if($confirm) {
				$this->model->deleteNews($id);
				$this->model->newsDestroyCats($id);
				WNote::success('article_deleted', WLang::get('article_deleted', $data['news_title']));
				header('location: ' . WRoute::getDir() . '/admin/news/');
			} else {
				$this->view->news_delete($data);
			}
		} else {
			WNote::error('article_not_found', WLang::get('article_not_found'));
			header('location: ' . WRoute::getDir() . '/admin/news/');
		}
	}

	/**
	 * Gestion des catégories
	 */
	protected function categories_manager() {
		// Préparation tri colonnes
		$args = WRoute::getArgs();
		if (isset($args[1])) {
			$sortData = explode('-', $args[1]);
		} else {
			$sortData = array();
		}
		
		if (empty($sortData)) {
			$sortBy = 'news_cat_name';
			$sens = 'ASC';
		} else {
			$sortBy = array_shift($sortData);
			$sens = !empty($sortData) ? $sortData[0] : 'ASC';
		}
		
		$catList = $this->model->getCatList($this->model->cats_data_model['toDB'][$sortBy], $sens == 'ASC');
		
		/**
		 * Formulaire pour l'AJOUT ou l'EDITION d'une catégorie
		 */
		$data = WRequest::getAssoc(array('news_cat_name', 'news_cat_shortname', 'news_cat_parent'));
		// On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
		if (!in_array(null, $data, true)) {
			$erreurs = array();
			
			if (empty($data['news_cat_name'])) {
				$erreurs[] = WLang::get('category_no_name');
			}
			
			// Formatage du nom racourci
			if (empty($data['news_cat_shortname'])) {
				$data['news_cat_shortname'] = strtolower($data['news_cat_name']);
			} else {
				$data['news_cat_shortname'] = strtolower($data['news_cat_shortname']);
			}
			$data['news_cat_shortname'] = preg_replace('#[^a-z0-9.]#', '-', $data['news_cat_shortname']);
			$data['news_cat_shortname'] = preg_replace('#-{2,}#', '-', $data['news_cat_shortname']);
			$data['news_cat_shortname'] = trim($data['news_cat_shortname'], '-');
			
			$data['news_cat_id'] = WRequest::get("news_cat_id");
			$edit = false;
			
			if (!empty($data['news_cat_id']) && $this->model->validExistingCatId(intval($data['news_cat_id']))) {
				$edit = true;
			} else {
				unset($data['news_cat_id']);
			}
			
			if (!empty($erreurs)) { // Il y a un problème
				WNote::error('data_errors', implode("<br />\n", $erreurs), 'assign');
			} else {
				if ($edit) {
					if ($this->model->updateCat($data)) {
						WNote::success('cat_edited', WLang::get('cat_edited', $data['news_cat_name']));
						header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
					} else {
						WNote::error('cat_not_edited', WLang::get('cat_not_edited'));
						$this->view->categories_manager($catList, $sortBy, $sens, $data);
						$this->view->render();
					}
				} else {
					if ($this->model->createCat($data)) {
						WNote::success('cat_added', WLang::get('cat_added', $data['news_cat_name']));
						header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
					} else {
						WNote::error('cat_not_added', WLang::get('cat_not_added'));
						$this->view->categories_manager($catList, $sortBy, $sens, $data);
						$this->view->render();
					}
				}
			}
		}
		$this->view->categories_manager($sortBy, $sens, $catList);
		$this->view->render();
	}

	protected function category_delete() {	
		$args = WRoute::getArgs();
		$id = -1;
		$confirm = false;
		
		if (!empty($args[1])) {
			$args = explode("-",$args[1]);
			
			$id = $args[0];
			
			if (!empty($args[1]) && $args[1] == "confirm") {
				$confirm = true;
			}
		}
		
		if ($this->model->validExistingCatId($id)) {		
			if($confirm) {
				$this->model->deleteCat($id);
				$this->model->catsDestroyNews($id);
				$this->model->unlinkChildren($id);
				WNote::success('category_deleted', WLang::get('category_deleted'));
				header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
			} else {
				$this->view->category_delete($id);
			}
		} else {
			WNote::error('category_not_found', WLang::get('category_not_found'));
			header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
		}
	}
}

?>
