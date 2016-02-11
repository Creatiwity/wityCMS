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
 * @version 0.5.0-11-02-2016
 */
class NewsController extends WController {
	protected function listing($params) {
		$cat_shortname = '';
		$id_news = 0;
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
				$id_news = intval($params[0]);
			}
		}

		if (!empty($id_news)) {
			$news = $this->model->getNews($id_news);

			if (empty($news)) {
				$this->setHeader('Location', WRoute::getDir().'news');
				return WNote::error('news_not_found', WLang::get('news_not_found'));
			}

			// Forbid access to non published news to non admin users
			if ($news['published'] != 1 && !$this->hasAccess('news', '', true)) {
				$this->setHeader('Location', WRoute::getDir().'news');
				return WNote::error('news_not_found', WLang::get('news_not_found'));
			}

			if (!empty($news['cats'])) {
				$category = $news['cats'][0];
				$cat_shortname = $category['shortname'];
			}

			$news_set = array($news);

			// Increase views
			$this->model->increaseViews($id_news);
		} else {
			$filter_cats = (empty($cat_shortname)) ? array() : array('cats' => array($cat_shortname));

			// Display unpublished news to admin
			if ($this->hasAccess('news', '', true)) {
				$filter_cats['published'] = -1;
				$filter_cats['publish_date'] = -1;
			}

			$news_set = $this->model->getAllNews(0, 4, 'created_date', false, $filter_cats);
		}

		return array(
			'news'            => $news_set,
			'categories'      => $categories,
			'cat_selected'    => $cat_shortname
		);
	}

	protected function preview($params) {
		if (!empty($_SESSION['access']) && !empty($_SESSION['news_preview'])) {
			$news = $_SESSION['news_preview'];

			unset($_SESSION['news_preview']);

			return array(
				'news' => array($news)
			);
		}
	}
}

?>
