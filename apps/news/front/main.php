<?php
/**
 * News Application - Front Controller - /apps/news/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsController is the Front Controller of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class NewsController extends WController {
	private function getId() {
		$option = $this->getOption(0);
		if (empty($option)) {
			return -1;
		} else {
			list($id) = explode('-', $option);
			return intval($id);
		}
	}
	
	public function listing() {
		$news_id = $this->getId();
		if ($news_id != -1 && !$this->model->validExistingNewsId($news_id)) {
			return WNote::error('news_not_found', "Error");
		}
		
		if ($news_id != -1) {
			return $this->display($news_id);
		} else {
			return $this->listNews();
		}
	}
	
	protected function listNews() {
		return $this->model->getNewsList(0, 3);
	}
	
	protected function display($news_id) {
		return array($this->model->getNews($news_id));
	}
}

?>