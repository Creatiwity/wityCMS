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
 * @version 0.5.0-dev-23-03-2015
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
		$this->assign('require', 'apps!page/form');
		$this->assign('js', "/libraries/ckeditor-4.4.7/ckeditor.js");
		$this->assign('require', 'witycms/admin');

		$this->assign('id', $model['id']);
		$this->assign('pages', $model['pages']);

		$lang_list = array(1, 2);
		$default = array(
			'id'            => 0,
			'image'         => '',
			'created_date'  => '',
			'modified_date' => ''
		);
		$default_translatable = array(
			'title'            => '',
			'subtitle'         => '',
			'content'          => '',
			'author'           => !empty($_SESSION['firstname']) || !empty($_SESSION['lastname']) ? trim($_SESSION['firstname'].' '.$_SESSION['lastname']) : $_SESSION['nickname'],
			'url'              => '',
			'meta_title'       => '',
			'meta_description' => '',
			'published'        => true,
		);
		
		foreach ($default_translatable as $key => $value) {
			foreach ($lang_list as $id_lang) {
				$default[$key.'_'.$id_lang] = $value;
			}
		}

		$this->assignDefault($default, $model['data']);

		$this->setTemplate('form');
	}

	public function add($model) {
		$this->form($model);
	}

	public function edit($model) {
		$this->form($model);
	}

	public function delete($model) {
		$this->assign('title', $model['title_1']);
		$this->assign('confirm_delete_url', '/admin/page/delete/'.$model['id'].'/confirm');
	}
}

?>
