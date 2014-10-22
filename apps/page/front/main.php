<?php
/**
 * Page Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageController is the Front Controller of the Page Application
 * 
 * @package Apps\Page\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-07-10-2014
 */
class PageController extends WController {
	protected function listing($params) {
		return $this->model->getPages(true);
	}
	
	protected function display(array $params) {
		$page_id = intval(array_shift($params));
		
		if (!$this->model->validExistingPageId($page_id)) {
			return WNote::error('page_not_found', WLang::get('page_not_found', $page_id));
		}
		
		$model = $this->model->getPage($page_id);
		
		// View + 1
		$this->model->increaseView($page_id);
		
		$parent_pages = explode('/', $model['parent']);
		$breadcrumb = array();
		foreach ($parent_pages as $parent_page_id) {
			$parent_page_data = $this->model->getPage($parent_page_id);
			
			if (!empty($parent_page_data)) {
				$breadcrumb[] = array(
					'href' => '/'.$parent_page_data['url'],
					'text' => $parent_page_data['short_title']
				);
			}
		}
		
		$breadcrumb[] = array(
			'text' => $model['short_title']
		);
		
		$main_page_id = $parent_pages[0];
		if (empty($main_page_id)) {
			$main_page_id = $page_id;
		}
		
		// Side menu pages
		$side_pages = $this->model->getChildPages($main_page_id);
		
		return array(
			'page'       => $model,
			'breadcrumb' => $breadcrumb,
			'side_pages' => $side_pages
		);
	}
}

?>