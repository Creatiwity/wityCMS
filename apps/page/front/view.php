<?php
/**
 * Page Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageView is the Front View of the Page Application
 * 
 * @package Apps\Page\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-07-10-2014
 */
class PageView extends WView {
	public function display(array $model) {
		$this->tpl->assign('breadcrumb', $model['breadcrumb']);
		$this->tpl->assign('side_pages', $model['side_pages']);
		$this->assign($model['page']);
		
		// Assign meta data
		$this->tpl->assign('wity_page_title', $model['page']['meta_title']);
		$this->tpl->assign('wity_page_description', $model['page']['description']);
		$this->tpl->assign('wity_page_keywords', $model['page']['keywords']);
	}
}

?>