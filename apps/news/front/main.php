<?php
/**
 * News Application - Front Controller
 */

defined('IN_WITY') or die('Access denied');

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
		$news_id = intval(array_shift($params));
		
		if (!empty($news_id)) {
			return $this->display($news_id);
		} else {
			return $this->listNews();
		}
	}
	
	protected function listNews() {
		return $this->model->getNewsList(0, 3);
	}
	
	protected function display($news_id) {
		$news_data = $this->model->getNews($news_id);
		
		if (empty($news_data)) {
			return WNote::error('news_not_found', WLang::get('news_not_found'));
		}
		
		return array($news_data);
	}
}

?>