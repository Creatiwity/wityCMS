<?php
/**
 * Search Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SearchView is the Front View of the Search Application
 *
 * @package Apps\Search\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.0-03-09-2016
 */
class SearchView extends WView {
	public function form($data) {
		$this->assign('css', '/apps/search/front/css/search.css');
		$this->assign($data);
	}
}

?>
