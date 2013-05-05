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
	public function __construct() {
		include 'model.php';
		$this->model = new NewsModel();
		
		include 'view.php';
		$this->setView(new NewsView($this->model));
	}
	
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[0])) {
			return -1;
		} else {
			list($id) = explode('-', $args[0]);
			return intval($id);
		}
	}
	
	public function listing() {
		$news_id = $this->getId();
		if (!empty($news_id) && !$this->model->validExistingNewsId($news_id)) {
			// display error
			$news_id = 0;
		}
		
		if (empty($news_id)) {
			return $this->display($news_id);
		} else {
			return $this->listNews();
		}
	}
	
	protected function listNews() {
		return $this->model->getNewsList(0, 3);
		// $this->view->listing();
	}
	
	protected function display($news_id) {
		return $this->model->getNews($news_id);
		// $this->view->detail($news_id);
	}
}

?>