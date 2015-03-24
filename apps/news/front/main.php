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
 * @version 0.5.0-dev-24-03-2015
 */
class NewsController extends WController {
	protected function listing(array $params) {
		$cat_shortname = '';
		$news_id = 0;
		$listing_view = true;
		$categories = $this->model->getCatsStructure();
		
		// URL may contain either a category, either a news ID:
		// Ex 1: /news/cat/test
		// Ex 2: /news/64-news-test
		if (!empty($params[0])) {
			if ($params[0] == 'cat') {
				$category = $this->model->getCatByShortname($params[1]);
				
				if (!empty($category)) {
					$cat_shortname = $category['shortname'];
				}
			} else {
				$news_id = intval($params[0]);
			}
		}
		
		if (!empty($news_id)) {
			$news_data = $this->model->getNews($news_id);
			
			if (empty($news_data)) {
				return WNote::error('news_not_found', WLang::get('news_not_found'));
			}
			
			if (!empty($news_data['cats'])) {
				$category = $news_data['cats'][0];
				$cat_shortname = $category['shortname'];
			}
			
			$news = array($news_data);
			
			// Increase views
			$this->model->increaseViews($news_id);
			
			$listing_view = false;
		} else {
			$filter_cats = (empty($cat_shortname)) ? array() : array('cats' => array($cat_shortname));
			$news = $this->model->getAllNews(0, 4, 'created_date', false, $filter_cats);
		}
		
		return array(
			'news'            => $news,
			'categories'      => $categories,
			'cat_selected'    => $cat_shortname,
			'listing_view'    => $listing_view
		);
	}
}

?>