<?php

/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * @author Fofif
 * @version	$Id: apps/news/admin/main.php 0002 01-08-2011 Fofif $
 */
class NewsAdminController extends WController {

        protected $news_data_model = array();
        protected $cats_data_model = array();

        /*
         * Chargement du modèle et de la view
         */

        public function __construct() {
                include 'model.php';
                $this->model = new NewsAdminModel();

                include 'view.php';
                $this->setView(new NewsAdminView());

                $this->news_data_model = array(
                    'toDB' => array(
                        'news_id' => 'id',
                        'news_url' => 'url',
                        'news_title' => 'title',
                        'news_author' => 'author',
                        'news_content' => 'content',
                        'news_keywords' => 'keywords',
                        'news_date' => 'date',
                        'news_modified' => 'modified',
                        'news_views' => 'views',
                        'news_cats' => 'cat',
                        'news_editor_id' => 'editor_id',
                        'news_image' => 'image'
                    ),
                    'fromDB' => array(
                        'id' => 'news_id',
                        'url' => 'news_url',
                        'title' => 'news_title',
                        'author' => 'news_author',
                        'content' => 'news_content',
                        'keywords' => 'news_keywords',
                        'date' => 'news_date',
                        'modified' => 'news_modified',
                        'views' => 'news_views',
                        'cat' => 'news_cats',
                        'editor_id' => 'news_editor_id',
                        'image' => 'news_image'
                    )
                );

                $this->cats_data_model = array(
                    'toDB' => array(
                        'news_cat_id' => 'cid',
                        'news_cat_name' => 'name',
                        'news_cat_shortname' => 'shortname',
                        'news_cat_parent' => 'parent'
                    ),
                    'fromDB' => array(
                        'cid' => 'news_cat_id',
                        'name' => 'news_cat_name',
                        'shortname' => 'news_cat_shortname',
                        'parent' => 'news_cat_parent'
                    )
                );
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

        protected function news_listing() {
                $args = WRoute::getArgs();
                $sortData = explode('-', array_shift($args));
                if (empty($sortData)) {
                        $sortBy = 'news_date';
                        $sens = 'DESC';
                } else {
                        $sortBy = array_shift($sortData);
                        $sens = !empty($sortData) ? $sortData[0] : 'DESC';

                        if (empty($this->news_data_model['toDB'][$sortBy])) {
                                $sortBy = 'news_date';
                        }
                }
                $newsList = $this->model->getNewsList(0, 100, $this->news_data_model, $this->news_data_model['toDB'][$sortBy], $sens == 'ASC', $this->cats_data_model);
                $this->view->news_listing($newsList, $sortBy, $sens);
                $this->view->render();
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
                                $erreurs[] = "Il manque un titre à l'article.";
                        }

                        if (empty($data['news_author'])) {
                                $erreurs[] = "Il manque un auteur à l'article.";
                        }

                        // Traitement du permalien
                        if (empty($data['news_url'])) {
                                $erreurs[] = "Aucun permalien (lien vers la news) n'a été défini.";
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
                                        $erreurs[] = "Erreur lors de l'upload de l'image à la une : " . $upload->error;
                                }
                                $data['news_image'] = $upload->file_dst_name;
                        } else {
                                $data['news_image'] = '';
                        }

                        if (!empty($erreurs)) { // Il y a un problème
                                WNote::error('data_errors', implode("<br />\n", $erreurs), 'assign');
                                $catList = $this->model->getCatList($this->cats_data_model, "name", "ASC");

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
                                        if ($this->model->createNews($data, $this->news_data_model)) {
                                                // Traitement des catégories
                                                if (!empty($data['news_cats'])) {
                                                        $nid = $this->model->getLastNewsId();
                                                        // Récupération des id des catégories stockées dans les clés du tableau
                                                        foreach ($data['news_cats'] as $cid => $v) {
                                                                $this->model->newsAddCat($nid, intval($cid));
                                                        }
                                                }

                                                WNote::success('article_added', "L'article <strong>" . $data['news_title'] . "</strong> a été créé avec succès.");
                                                header('location: ' . WRoute::getDir() . '/admin/news/');
                                        } else {
                                                WNote::error('article_not_added', "Une erreur inconnue s'est produite.");
                                                $catList = $this->model->getCatList($this->cats_data_model, "name", "ASC");
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
                                        if ($this->model->updateNews($data, $this->news_data_model)) {
                                                WNote::success('article_edited', "L'article <strong>" . $data['news_title'] . "</strong> a été modifié avec succès.");
                                                header('location: ' . WRoute::getDir() . '/admin/news/');
                                        } else {
                                                WNote::error('article_not_edited', "Une erreur inconnue s'est produite.");
                                                $catList = $this->model->getCatList($this->cats_data_model, "name", "ASC");
                                                $this->view->news_add_or_edit($catList, $id, $data);
                                        }
                                }
                        }
                } else {
                        $id = $this->getId();

                        $catList = $this->model->getCatList($this->cats_data_model, "name", "ASC");

                        if ($this->model->validExistingNewsId($id)) {
                                $data = $this->model->loadNews($id, $this->news_data_model);
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
                        $data = $this->model->loadNews($id, $this->news_data_model);
			
			if($confirm) {
				$this->model->deleteNews($id);
				$this->model->newsDestroyCats($id);
				WNote::success('article_deleted', "L'article \"<strong>" . $data['news_title'] . "</strong>\" a été supprimé avec succès.");
				header('location: ' . WRoute::getDir() . '/admin/news/');
			} else {
				$this->view->news_delete($data);
			}
                } else {
                        WNote::error('article_not_found', "L'article que vous tentez de supprimer n'existe pas.");
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

                $catList = $this->model->getCatList($this->cats_data_model, $this->cats_data_model['toDB'][$sortBy], $sens == 'ASC');

                /**
                 * Formulaire pour l'AJOUT ou l'EDITION d'une catégorie
                 */
                $data = WRequest::getAssoc(array('news_cat_name', 'news_cat_shortname', 'news_cat_parent'));
                // On vérifie que le formulaire a été envoyé par la non présence d'une valeur "null" cf WRequest
                if (!in_array(null, $data, true)) {
                        $erreurs = array();

                        if (empty($data['news_cat_name'])) {
                                $erreurs[] = "Veuillez spécifier un nom pour la nouvelle catégorie.";
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
                                if (edit) {
                                        if ($this->model->updateCat($data, $this->cats_data_model)) {
                                                WNote::success('cat_edited', "La catégorie <strong>" . $data['news_cat_name'] . "</strong> a été éditée avec succès.");
                                        } else {
                                                WNote::error('cat_not_edited', "Une erreur inconnue s'est produite.");
                                                $this->view->categories_manager($catList, $sortBy, $sens, $data);
                                                $this->view->render();
                                        }
                                } else {
                                        if ($this->model->createCat($data, $this->cats_data_model)) {
                                                WNote::success('cat_added', "La catégorie <strong>" . $data['news_cat_name'] . "</strong> a été ajoutée avec succès.");
                                        } else {
                                                WNote::error('cat_not_added', "Une erreur inconnue s'est produite.");
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
		
		if(!empty($args[1])) {
			$args = explode("-",$args[1]);
			
			$id = $args[0];
			
			if(!empty($args[1]) && $args[1] == "confirm") {
				$confirm = true;
			}
		}

		if ($this->model->validExistingCatId($id)) {			
			if($confirm) {
				$this->model->deleteCat($id);
				$this->model->catsDestroyNews($id);
				WNote::success('category_deleted', "La catégorie a été supprimée avec succès.");
				header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
			} else {
				$this->view->category_delete($id);
			}
                } else {
                        WNote::error('category_not_found', "La catégorie que vous tentez de supprimer n'existe pas.");
                        header('location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
                }
        }

}

?>