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
 * @version 0.3-19-04-2013
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
		$this->assign('js', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.min.js");
		
		// Assign site URL for permalink management
		$this->assign('siteURL', WRoute::getBase() . '/news/');
		$this->assign('lastId', $model['news_id']);
		
		$cat_ids = array();
		if (!empty($model['data']['news_cats']) && is_array($model['data']['news_cats'])) {
			foreach ($model['data']['news_cats'] as $key => $cat) {
				if ($cat === 'on') {
					$cat_ids[] = $key;
				} else if (is_array($cat)) {
					$cat_ids[] = $cat['news_cat_id'];
				}
			}
		}
		$this->assign('cats_list', $model['cats_list']);
		$this->assign('news_cats', $cat_ids);
		
		$this->fillMainForm(array(
			'news_author' => $_SESSION['nickname'],
			'news_keywords' => '',
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
	
	public function news_delete($data) {
		$this->assign('title', $data['news_title']);
		$this->assign('confirm_delete_url', "/admin/news/news_delete/".$data['news_id']."/confirm");
		$this->setTheme('_blank');
	}
	
	public function category_delete($id) {
		$this->assign('confirm_delete_url', "/admin/news/category_delete/".$id."/confirm");
		$this->setTheme('_blank');
	}
	
	public function categories_manager($model) {
		$this->assign('js', '/apps/news/admin/js/categories_manager.js');
		$this->assign($model['adminStyle']->getTplVars());
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
