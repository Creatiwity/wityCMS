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
		$this->assign('js', "/libraries/ckeditor-4.4.5/ckeditor.js");
		
		// Assign site URL for permalink management
		$this->assign('id', $model['id']);
		
		$this->assign('pages', $model['pages']);
		
		$this->assignDefault(array(
			'title'         => '',
			'content'       => '',
			'url'           => '',
			'meta_title'    => '',
			'meta_description' => '',
			'author'        => $_SESSION['nickname'],
			'subtitle'      => '',
			'parent'        => '',
			'image'         => '',
			'created_date'  => '',
			'modified_date' => '',
		), $model['data']);
		
		$this->setTemplate('form');
	}
	
	public function edit($model) {
		$this->form($model);
	}
	
	public function delete($model) {
		$this->assign('title', $model['title']);
		$this->assign('confirm_delete_url', '/admin/page/delete/'.$model['id'].'/confirm');
	}
}

?>
