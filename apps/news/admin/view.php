<?php
/**
 * News Application - Admin View - /apps/news/admin/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminView is the Admin View of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johandufau@gmail.com>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-15-02-2013
 */
class NewsAdminView extends WView {
	public function news_listing($data = array(), $adminStyle, $pagination) {
		$sorting = $adminStyle->getSorting();
		
		$this->assign('pagination', $pagination->getHTML());
		
		// Enregistrement des variables de classement
		$this->assign($adminStyle->getTplVars());
		$this->assign('news', $data);
		
		$this->render('news_listing');
	}

	/**
	 * Définition des valeurs de contenu du formulaire
	 */
	private function fillMainForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function news_form($catList = array(), $lastId = '0', $data = array()) {
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
		
		$ids = array();
		if (!empty($data['news_cats']) && is_array($data['news_cats'])) {
			foreach ($data['news_cats'] as $row => $val) {
				$ids[] = $row;
			}
		}
		$this->assign('news_cats', $ids);
		
		$this->fillMainForm(array(
			'news_author' => $_SESSION['nickname'],
			'news_keywords' => '',
			'news_title' => '',
			'news_url' => '',
			'news_content' => '',
			'news_date' => '',
			'news_modified' => ''
		), $data);
		
		$this->render('news_form');
	}
	
	public function news_delete($data = array()) {
		$this->assign('title', $data['news_title']);
		$this->assign('confirm_delete_url', WRoute::getDir()."/admin/news/news_delete/".$data['news_id']."/confirm");
		$this->tpl->assign($this->vars);
		echo $this->tpl->parse('/apps/news/admin/templates/delete_news.html');
	}
	
	public function category_delete($id) {
		$this->assign('confirm_delete_url', WRoute::getDir()."/admin/news/category_delete/".$id."-confirm");
		$this->tpl->assign($this->vars);
		echo $this->tpl->parse('/apps/news/admin/templates/delete_category.html');
	}
	
	public function categories_manager($cats_list, $adminStyle, $post_data = array()) {
		$this->assign('js', '/apps/news/admin/js/categories_manager.js');
		$this->assign($adminStyle->getTplVars());
		$this->fillMainForm(array(
			'news_cat_id' => '',
			'news_cat_name' => '',
			'news_cat_shortname' => '',
			'news_cat_parent' => 0,
			'news_cat_parent_name' => ""
		), $post_data);
		$this->assign('cats', $cats_list);
		$this->render('categories_manager');
	}
}

?>
