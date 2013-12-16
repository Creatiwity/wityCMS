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
		$this->assign($model['sorting_tpl']);

		$pagination = WHelper::load('pagination', array(
			$model['total'],
			$model['per_page'],
			$model['current_page'],
			'/admin/news/listing/'.$model['sorting_vars'][0].'-'.$model['sorting_vars'][1].'-%d'
		));
		$this->assign('pagination', $pagination->getHTML());

		$this->assign('news', $model['data']);

		$this->setTemplate('news_listing');
	}

	public function news_form($model) {
		// JS / CSS
		$this->assign('css', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.css");
		$this->assign('require', 'apps!news/add_or_edit');
		$this->assign('require', 'wysihtml5');

		// Assign site URL for permalink management
		$this->assign('site_url', WRoute::getBase() . '/news/');

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
		$this->assign('cats', $model['cats']);
		$this->assign('news_cats', $news_cats);

		$this->assignDefault(array(
			'news_id'          => 0,
			'news_author'      => $_SESSION['nickname'],
			'news_meta_title'  => '',
			'news_keywords'    => '',
			'news_description' => '',
			'news_title'       => '',
			'news_url'         => '',
			'news_content'     => '',
			'news_date'        => '',
			'news_modified'    => ''
		), $model['data']);
		$this->setTemplate('news_form');
	}

	public function add($model) {
		$this->news_form($model);
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
		$this->assign('require', 'apps!news/categories_manager');

		$this->assign($model['sorting_tpl']);
		$this->assign('cats', $model['data']);

		$this->assignDefault(array(
			'news_cat_id'          => '',
			'news_cat_name'        => '',
			'news_cat_shortname'   => '',
			'news_cat_parent'      => 0,
			'news_cat_parent_name' => ""
		), $model['post_data']);
	}
}

?>
