<?php
/**
 * News Application - Front Model
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsModel is the Front Model of the News Application
 *
 * @package Apps\News\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-19-04-2013
 */
class NewsModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		// Declare table
		$this->db->declareTable('news');
		$this->db->declareTable('news_cats');
		$this->db->declareTable('news_cats_relations');
	}
	
	/**
	 * Counts news in the database
	 * 
	 * @return int
	 */
	public function countNews(array $filters = array()) {
		$cond = '';
		if (!empty($filters['cats'])) {
			$cond .= 'AND (1 = 0 ';
			foreach ($filters['cats'] as $cat) {
				$cond .= 'OR shortname = "'.$cat.'" ';
			}
			$cond .= ') ';
		}
		
		if (!empty($filters['published'])) {
			$published = intval($filters['published']);
			if ($published == 0 || $published == 1) {
				$cond .= 'AND published = '.$published.' ';
			}
		} else {
			$cond .= 'AND published = 1 ';
		}
		
		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM news
			LEFT JOIN news_cats_relations
			ON id = news_cats_relations.id_news
			LEFT JOIN news_cats
			ON id_cat = cid
			WHERE 1 = 1 '.$cond.'
		');
		$prep->execute();
		
		return intval($prep->fetchColumn());
	}
	
	/**
	 * Retrieves a set of news
	 * 
	 * @param int $from
	 * @param int $number
	 * @param string $order Ordering field name
	 * @param bool $asc true = ASC order / false = DESC order
	 * @param array $filters Set of filters: cats(array), published(int(1|0))
	 * @return array
	 */
	public function getNewsList($from, $number, $order = 'created_date', $asc = false, array $filters = array()) {
		$cond = '';
		if (!empty($filters['cats'])) {
			$cond .= 'AND (1 = 0 ';
			foreach ($filters['cats'] as $cat) {
				$cond .= 'OR shortname = "'.$cat.'" ';
			}
			$cond .= ') ';
		}
		
		if (!empty($filters['published'])) {
			$published = intval($filters['published']);
			if ($published == 0 || $published == 1) {
				$cond .= 'AND published = '.$published.' ';
			}
		} else {
			$cond .= 'AND published = 1 ';
		}
		
		$prep = $this->db->prepare('
			SELECT DISTINCT(id), url, views, image, news.created_date, news.modified_date, news.modified_by,
				title, author, content, meta_title, keywords, description, published
			FROM news
			LEFT JOIN news_cats_relations
			ON id = news_cats_relations.id_news
			LEFT JOIN news_cats
			ON id_cat = cid
			WHERE 1 = 1 '.$cond.'
			ORDER BY news.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		
		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$data['cats'] = $this->getCatsOfNews($data['id']);
			
			$date = new WDate($data['created_date']);
			$timestamp = $date->getTimestamp();
			$data['date_day'] = strftime('%d', $timestamp);
			$data['date_month'] = substr(strftime('%B', $timestamp), 0, 3);
			$data['date_year'] = strftime('%Y', $timestamp);
			
			$result[] = $data;
		}
		
		return $result;
	}
	
	/**
	 * Retrieves all data linked to a News
	 * 
	 * @param int $id_news
	 * @return array
	 */
	public function getNews($id_news) {
		if (empty($id_news)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT id, url, views, image, created_date, modified_date, modified_by,
				title, author, content, meta_title, keywords, description, published
			FROM news
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->execute();
		
		$data = $prep->fetch(PDO::FETCH_ASSOC);
		
		if (!empty($data)) {
			$data['cats'] = $this->getCatsOfNews($id_news);
		}
		
		$date = new WDate($data['created_date']);
		$timestamp = $date->getTimestamp();
		$data['date_day'] = strftime('%d', $timestamp);
		$data['date_month'] = strftime('%B', $timestamp);
		$data['date_year'] = strftime('%Y', $timestamp);
		
		return $data;
	}
	
	/**
	 * Retrieves a category from the database
	 * 
	 * @param int $id_cat
	 * @return array
	 */
	public function getCat($id_cat) {
		if (empty($id_cat)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT cid, name, shortname
			FROM news_cats 
			WHERE cid = :id_cat
		');
		$prep->bindParam(':id_cat', $id_cat, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves a category by its shortname from the database.
	 * 
	 * @param int $cat_id
	 * @return array
	 */
	public function getCatByShortname($shortname) {
		if (empty($shortname)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT cid, name, shortname, parent
			FROM news_cats
			WHERE shortname = :shortname
		');
		$prep->bindParam(':shortname', $shortname);
		$prep->execute();
		
		$data = $prep->fetch(PDO::FETCH_ASSOC);
		
		return $data;
	}
	
	/**
	 * Retrieves a complete set of Categories linked to News
	 * 
	 * @param int $id_news
	 * @return array
	 */
	public function getCatsOfNews($id_news) {
		$prep = $this->db->prepare('
			SELECT cid, name, shortname
			FROM news_cats_relations
			LEFT JOIN news_cats
			ON id_cat = cid
			WHERE id_news = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves full set of Categories in the database
	 * 
	 * @param string $order
	 * @param bool $asc
	 * @return array
	 */
	public function getCatsList($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT cid, name, shortname, parent
			FROM news_cats
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();
		
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		// Find parent categories' name
		foreach ($result as $key => $cat) {
			$result[$key]['parent_name'] = "";
			if ($cat['parent'] != 0) {
				foreach ($result as $key2 => $cat2) {
					if ($cat2['cid'] == $cat['parent']) {
						$result[$key]['parent_name'] = $cat2['name'];
						break;
					}
				}
			}
		}
		
		return $result;
	}
	
	public function increaseViews($news_id) {
		$prep = $this->db->prepare('
			UPDATE news
			SET views = views + 1
			WHERE id = :id
		');
		$prep->bindParam(':id', $news_id);
		
		return $prep->execute();
	}
}

?>