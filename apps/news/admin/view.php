<?php
/**
 * News Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsAdminView is the Admin View of the News Application
 * 
 * @package Apps\News\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-19-04-2013
 */
class NewsAdminView extends WView {
	public function listing($model) {
		$sorting = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());
		
		$pagination = WHelper::load('pagination', array($model['total'], $model['news_per_page'], $model['current_page'], '/admin/news/'.$sorting[0].'-'.$sorting[1].'-%d/'));
		$this->assign('pagination', $pagination->getHTML());
		
		$this->assign('news', $model['data']);
		
		$this->setTemplate('news_listing');
	}

	/**
	 * Function to define template variable from a default array structure
	 */
	private function fillMainForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function news_form($model) {
		// JS / CSS
		$this->assign('js', '/apps/news/admin/js/add_or_edit.js');
		$this->assign('css', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.css");
		$this->assign('js', "/libraries/wysihtml5-bootstrap/wysihtml5.min.js");
		$this->assign('js', "/libraries/wysihtml5-bootstrap/bootstrap3-wysihtml5.js");
		
		// Assign site URL for permalink management
		$this->assign('site_url', WRoute::getBase() . '/news/');
		$this->assign('news_id', $model['news_id']);
		$this->assign('last_id', $model['last_id']);
		
		// Treat categories filled by user
		$news_cats = array();
		if (isset($model['data']['news_cats']) && is_array($model['data']['news_cats'])) {
			foreach ($model['data']['news_cats'] as $key => $cat) {
				if ($cat === 'on') {
					$news_cats[] = $key;
				} else if (is_array($cat)) {
					$news_cats[] = $cat['news_cat_id'];
				}
			}
		}
		$this->assign('cats_list', $model['cats_list']);
		$this->assign('news_cats', $news_cats);
		
		$this->fillMainForm(array(
			'news_author' => $_SESSION['nickname'],
			'news_meta_title' => '',
			'news_keywords' => '',
			'news_description' => '',
			'news_title' => '',
			'news_url' => '',
			'news_content' => '',
			'news_date' => '',
			'news_modified' => ''
		), $model['data']);
		$this->setTemplate('news_form');
	}
	
	public function edit($model) {
		$this->news_form($model);
	}
	
	public function news_delete($model) {
		$this->assign('title', $model['news_title']);
		$this->assign('confirm_delete_url', "/admin/news/news_delete/".$model['news_id']."/confirm");
	}
	
	public function category_delete($model) {
		$this->assign('confirm_delete_url', "/admin/news/category_delete/".$model['cat_id']."/confirm");
	}
	
	public function categories_manager($model) {
		$this->assign('js', '/apps/news/admin/js/categories_manager.js');
		$this->assign($model['sortingHelper']->getTplVars());
		$this->fillMainForm(array(
			'news_cat_id' => '',
			'news_cat_name' => '',
			'news_cat_shortname' => '',
			'news_cat_parent' => 0,
			'news_cat_parent_name' => ""
		), $model['post_data']);
		$this->assign('cats', $model['data']);
	}
}

?>
