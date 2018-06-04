<?php
/**
 * Page Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageAdminView is the Admin View of the Page Application
 *
 * @package Apps\Page\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class PageAdminView extends WView {
	public function pages($model) {
		$pagination = WHelper::load('pagination', array(
			$model['total'],
			$model['page_per_page'],
			$model['current_page'],
			'/admin/page/%d'
		));
		$this->assign('pagination', $pagination->getHTML());

		$this->assign('pages', $model['data']);
	}

	public function form($model) {
		// JS & CSS
		$this->assign('require', 'witycms/admin');
		$this->assign('require', 'apps!page/form');
		$this->assign('js', '/libraries/ckeditor/ckeditor.js');

		$this->assign('id', $model['id']);
		$this->assign('pages', $model['pages']);

		$default = array(
			'id'            => 0,
			'parent'        => '',
			'image'         => '',
			'created_date'  => '',
			'modified_date' => ''
		);
		$default_translatable = array(
			'title'            => '',
			'subtitle'         => '',
			'author'           => !empty($_SESSION['firstname']) || !empty($_SESSION['lastname']) ? trim($_SESSION['firstname'].' '.$_SESSION['lastname']) : $_SESSION['nickname'],
			'content'          => '',
			'url'              => '',
			'meta_title'       => '',
			'meta_description' => '',
		);

		$lang_list = WLang::getLangIds();
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

		$this->setTemplate('form.html');
	}

	public function add($model) {
		$this->form($model);
	}

	public function edit($model) {
		$this->form($model);
	}

	public function delete($model) {
		$this->assign('title', $model['title_'.WLang::getLangId()]);
		$this->assign('confirm_delete_url', '/admin/page/delete/'.$model['id'].'/confirm');
	}
}

?>
