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
 * @version 0.5.0-dev-24-03-2015
 */
class NewsAdminView extends WView {
	public function __construct() {
		parent::__construct();
		
		$this->assign('css', '/apps/news/admin/css/news-admin.css');
	}
	
	public function news($model) {
		$this->assign($model['sorting_tpl']);

		$pagination = WHelper::load('pagination', array(
			$model['total'],
			$model['per_page'],
			$model['current_page'],
			'/admin/news/news/'.$model['sorting_vars'][0].'-'.$model['sorting_vars'][1].'-%d'
		));
		$this->assign('pagination', $pagination->getHTML());

		$this->assign('news', $model['data']);
	}

	public function newsForm($model) {
		// JS / CSS
		$this->assign('js', '/libraries/ckeditor-4.4.5/ckeditor.js');
		$this->assign('require', 'apps!news/news-form');

		// Assign site URL for permalink management
		$this->assign('site_url', WRoute::getBase().'news/');

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
			'id'               => 0,
			'url'              => '',
			'title'            => '',
			'author'           => !empty($_SESSION['firstname']) || !empty($_SESSION['lastname']) ? trim($_SESSION['firstname'].' '.$_SESSION['lastname']) : $_SESSION['nickname'],
			'content'          => '',
			'meta_title'       => '',
			'meta_description' => '',
			'published'        => true,
			'image'            => '',
			'created_date'     => '',
			'modified_date'    => '',
		);
		
		$this->assignDefault($default, $model['data']);
		$this->setTemplate('news-form');
	}

	public function newsAdd($model) {
		$this->newsForm($model);
	}

	public function newsEdit($model) {
		$this->newsForm($model);
	}

	public function newsDelete($model) {
		$this->assign('title', $model['title']);
		$this->assign('confirm_delete_url', '/admin/news/news-delete/'.$model['id'].'/confirm');
	}

	public function categories($model) {
		$this->assign('require', 'apps!news/categories');

		$this->assign($model['sorting_tpl']);
		$this->assign('cats', $model['data']);

		$this->assignDefault(array(
			'id'          => '',
			'name'        => '',
			'shortname'   => '',
			'parent'      => 0,
			'parent_name' => ''
		), $model['post_data']);
	}

	public function categoryDelete($model) {
		$this->assign('confirm_delete_url', '/admin/news/category-delete/'.$model['cid'].'/confirm');
	}
}

?>
