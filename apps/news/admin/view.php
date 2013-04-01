<?php

/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * @author Fofif
 * @version	$Id: apps/news/admin/view.php 0002 01-08-2011 Fofif $
 */
class NewsAdminView extends WView {

        public function news_listing($data = array(), $sortBy = 'news_date', $sens = 'DESC') {
                // AdminStyle Helper
                $orderingFields = array('news_id', 'news_title', 'news_author', 'news_date', 'news_views');
                $adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'news_date', 'DESC'));

                // Sorting vars
                $adminStyle->getSorting($sortBy, $sens);

                // Enregistrement des variables de classement
                $this->assign($adminStyle->getTplVars());
                $this->assign('news', $data);
                $this->setResponse('news_listing');
        }

        /**
         * Définition des valeurs de contenu du formulaire
         */
        private function fillMainForm($model, $data) {
                foreach ($model as $item => $default) {
                        $this->assign($item, isset($data[$item]) ? $data[$item] : $default);
                }
        }

        public function news_add_or_edit($catList = array(), $lastId = '0', $data = array()) {
                // JS / CSS
                $this->assign('js', '/apps/news/admin/js/add_or_edit.js');

                $this->assign('baseDir', WRoute::getDir());

                // Assignation de l'adresse du site pour le permalien
                $this->assign('siteURL', WRoute::getBase() . '/news/');

                // Chargement des catégories
                $this->assign('cat', $catList);

                // Id pour simuler le permalien
                $this->assign('lastId', $lastId);

                $this->assign('css', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.css");
                $this->assign('js', "/libraries/wysihtml5-bootstrap/wysihtml5.min.js");
                $this->assign('js', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.min.js");
                $this->assign('js', "/libraries/wysihtml5-bootstrap/locales/bootstrap-wysihtml5.fr-FR.js");

                $ids = array();
                if (!empty($data)) {
                        foreach ($data['news_cats'] as $row => $val) {
                                $ids[] = $row;
                        }
                }
                $this->assign('news_cats', $ids);

                $this->fillMainForm(
                        array(
                    'news_author' => $_SESSION['nickname'],
                    'news_keywords' => '',
                    'news_title' => '',
                    'news_url' => '',
                    'news_content' => '',
                    'news_date' => '',
                    'news_modified' => ''
                        ), $data
                );

                $this->setResponse('news_add_or_edit');
                $this->render();
        }
	
	public function news_delete($data = array()) {
		$this->assign('title', $data['news_title']);
		$this->assign('confirm_delete_url', WRoute::getDir()."/admin/news/news_delete/".$data['news_id']."-confirm");
		$this->tpl->assign($this->vars);
		echo $this->tpl->parse('/apps/news/admin/templates/delete_news.html');
	}
	
	public function category_delete($id) {
		$this->assign('confirm_delete_url', WRoute::getDir()."/admin/news/category_delete/".$id."-confirm");
		$this->tpl->assign($this->vars);
		echo $this->tpl->parse('/apps/news/admin/templates/delete_category.html');
	}

        public function categories_manager($sortBy, $sens, $data = array(), $fields = array()) {
                $this->assign('js', '/apps/news/admin/js/cat.js');

                // AdminStyle Helper
                $orderingFields = array('news_cat_name', 'news_cat_shortname');
                $adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'news_cat_name'));

                // Sorting vars
                $adminStyle->getSorting($sortBy, $sens);

                // Enregistrement des variables de classement
                $this->tpl->assign($adminStyle->getTplVars());

                $this->fillMainForm(
                        array(
                    'news_cat_id' => '',
                    'news_cat_name' => '',
                    'news_cat_shortname' => '',
                    'news_cat_parent' => 0
                        ), $fields
                );

                $this->assign('cats', $data);

                $this->setResponse('categories_manager');
        }

}

?>
