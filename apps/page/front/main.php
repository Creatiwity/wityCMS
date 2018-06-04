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
 * @version 0.6.2-04-06-2018
 */
class PageController extends WController {
	protected function display(array $params) {
		$id_page = intval(array_shift($params));

		$page = $this->model->getPage($id_page);

		if (empty($page)) {
			return WNote::error('page_not_found', WLang::get('Page not found.', $id_page));
		}

		// View + 1
		$this->model->increaseView($id_page);

		$parent_pages = explode('/', $page['parent']);
		$main_id_page = $parent_pages[0];

		if (empty($main_id_page)) {
			$main_id_page = $id_page;
		}

		// Side menu pages
		$side_pages = $this->model->getChildPages($main_id_page);

		return array(
			'page'       => $page,
			'side_pages' => $side_pages
		);
	}
}

?>
