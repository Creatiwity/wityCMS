<?php
/**
 * News Application - Front View - /apps/news/front/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsView is the Front View of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class NewsView extends WView {
	public function listing($data) {
		$this->assign('news', $data);
	}
	
	public function detail($data) {
		$data['news_content'] = str_replace('<hr />', '', $data['news_content']);
		$this->assign($data);
	}
}

?>