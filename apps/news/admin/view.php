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
 * @version 0.6.2-04-06-2018
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
		$this->assign('js', '/libraries/ckeditor/ckeditor.js');
		$this->assign('require', 'apps!news/news-form');
		$this->assign('require', 'witycms/admin');

		// Assign site URL for permalink management
		$this->assign('site_url', WRoute::getBase().'news/');

		// Treat categories input by user
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

		$lang_list = WLang::getLangIds();

		foreach ($lang_list as $id_lang) {
			if (!empty($model['data']['publish_date_'.$id_lang])) {
				if ($model['data']['publish_date_'.$id_lang] == '0000-00-00 00:00:00') {
					$model['data']['publish_date_'.$id_lang] = date('Y-m-d', time());
					$model['data']['publish_time_'.$id_lang] = date('H:i', time());
				} else {
					$datetime = explode(' ', $model['data']['publish_date_'.$id_lang]);

					$model['data']['publish_date_'.$id_lang] = $datetime[0];
					$model['data']['publish_time_'.$id_lang] = $datetime[1];
				}
			}
		}

		$default = array(
			'id'            => 0,
			'image'         => '',
			'created_date'  => '',
			'modified_date' => ''
		);
		$default_translatable = array(
			'title'            => '',
			'content'          => '',
			'author'           => !empty($_SESSION['firstname']) || !empty($_SESSION['lastname']) ? trim($_SESSION['firstname'].' '.$_SESSION['lastname']) : $_SESSION['nickname'],
			'url'              => '',
			'meta_title'       => '',
			'meta_description' => '',
			'published'        => true,
			'publish_date'     => date('Y-m-d', time()),
			'publish_time'     => date('H:i', time()),
		);

		foreach ($default_translatable as $key => $value) {
			foreach ($lang_list as $id_lang) {
				$default[$key.'_'.$id_lang] = $value;
			}
		}

		$this->assignDefault($default, $model['data']);

		// Auto-translate
		$form_values = array();
		foreach ($default as $item => $def) {
			$form_values[$item] = isset($model['data'][$item]) ? $model['data'][$item] : $def;
		}
		$this->assign('form_values', json_encode($form_values));

		$this->setTemplate('news-form.html');
	}

	public function newsAdd($model) {
		$this->newsForm($model);
	}

	public function newsEdit($model) {
		$this->newsForm($model);
	}

	public function newsDelete($model) {
		$this->assign('title', $model['title_'.WLang::getLangId()]);
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
