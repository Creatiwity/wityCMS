<?php
/**
 * News Application - Admin View
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminView is the Admin View of the News Application
 *
 * @package Apps\News\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-19-04-2013
 */
class NewsAdminView extends WView {
	public function __construct() {
		parent::__construct();
		
		$this->assign('css', '/apps/news/admin/css/news-admin.css');
	}
	
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
		$this->assign('js', "/libraries/ckeditor-4.4.5/ckeditor.js");
		$this->assign('require', 'apps!news/add_or_edit');

		// Assign site URL for permalink management
		$this->assign('site_url', WRoute::getBase() . 'news/');

		// Treat categories filled by user
		$cats = array();
		if (!empty($model['data']['cats']) && is_array($model['data']['cats'])) {
			foreach ($model['data']['cats'] as $key => $cat) {
				if ($cat === 'on') {
					$cats[] = $key;
				} else if (is_array($cat)) {
					$cats[] = $cat['cid'];
				}
			}
		}
		$this->assign('categories', $model['cats']);
		$this->assign('cats', $cats);
		
		$default = array(
			'id'            => 0,
			'url'           => '',
			'image'         => '',
			'created_date'  => '',
			'modified_date' => '',
			'author'      => $_SESSION['nickname'],
			'meta_title'  => '',
			'keywords'    => '',
			'description' => '',
			'title'       => '',
			'content'     => '',
			'published'   => true,
		);
		
		$this->assignDefault($default, $model['data']);
		$this->setTemplate('news_form');
	}

	public function add($model) {
		$this->news_form($model);
	}

	public function edit($model) {
		$this->news_form($model);
	}

	public function news_delete($model) {
		$this->assign('title', $model['title']);
		$this->assign('confirm_delete_url', "/admin/news/news_delete/".$model['id']."/confirm");
	}

	public function category_delete($model) {
		$this->assign('confirm_delete_url', "/admin/news/category_delete/".$model['cid']."/confirm");
	}

	public function categories_manager($model) {
		$this->assign('require', 'apps!news/categories_manager');

		$this->assign($model['sorting_tpl']);
		$this->assign('cats', $model['data']);

		$this->assignDefault(array(
			'id'          => '',
			'name'        => '',
			'shortname'   => '',
			'parent'      => 0,
			'parent_name' => ""
		), $model['post_data']);
	}
}

?>
