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
 * @version 0.5.0-dev-07-10-2014
 */
class PageAdminView extends WView {
	public function listing($model) {
		$sorting = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());
		
		$pagination = WHelper::load('pagination', array(
			$model['total'], 
			$model['page_per_page'], 
			$model['current_page'], 
			'/admin/page/'.$sorting[0].'-'.$sorting[1].'-%d/'
		));
		$this->assign('pagination', $pagination->getHTML());
		
		$this->assign('pages', $model['data']);
	}

	/**
	 * Function to define template variable from a default array structure
	 */
	private function fillMainForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function form($model) {
		// JS & CSS
		$this->assign('require', 'apps!page/form');
		$this->assign('js', "/libraries/ckeditor-4.4.5/ckeditor.js");
		
		// Assign site URL for permalink management
		$this->assign('id', $model['id']);
		
		$this->assign('pages', $model['pages']);
		
		$this->fillMainForm(array(
			'author'        => $_SESSION['nickname'],
			'meta_title'    => '',
			'short_title'   => '',
			'keywords'      => '',
			'description'   => '',
			'title'         => '',
			'subtitle'      => '',
			'url'           => '',
			'content'       => '',
			'parent'        => '',
			'date'          => '',
			'modified_date' => '',
			'image'         => ''
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
