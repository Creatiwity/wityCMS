<?php
/**
 * News Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsController is the Front Controller of the News Application
 * 
 * @package Apps\News\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-19-04-2013
 */
class NewsController extends WController {
	protected function listing(array $params) {
		$news_id = -1;
		
		if (isset($params[0])) {
			$id = intval($params[0]);
			if (!empty($id)) {
				$news_id = $id;
			}
		}
		
		if ($news_id != -1 && !$this->model->validExistingNewsId($news_id)) {
			return WNote::error('news_not_found', "The news #".$news_id." was not found.");
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