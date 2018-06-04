<?php
/**
 * News Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsView is the Front View of the News Application
 *
 * @package Apps\News\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class NewsView extends WView {
	public function listing($data) {
		$this->assign('css', '/apps/news/front/css/news.css');

		foreach ($data['news'] as $key => $news) {
			$data['news'][$key]['permalink'] = $news['url'];
			$data['news'][$key]['url'] = '/news/'.$news['id'].'-'.$news['url'];
		}

		$this->assign($data);
	}

	public function preview($data) {
		$this->assign($data);

		$this->setTemplate('listing.html');
	}
}

?>
