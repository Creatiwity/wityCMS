<?php
/**
 * News Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsModel is the Front Model of the News Application
 *
 * @package Apps\News\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
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
		$this->db->declareTable('news_lang');
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

		if (empty($filters['publish_date'])) {
			$cond .= 'AND publish_date <= NOW()';
		} else if ($filters['publish_date'] != -1) {
			$cond .= 'AND publish_date <= "'.$filters['publish_date'].'"';
		}

		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM news
			LEFT JOIN news_cats_relations
			ON id = news_cats_relations.id_news
			LEFT JOIN news_cats
			ON id_cat = cid
			LEFT JOIN news_lang
			ON id = news_lang.id_news AND id_lang = :id_lang
			WHERE 1 = 1 '.$cond.'
		');
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
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
	public function getAllNews($from, $number, $order = 'created_date', $asc = false, array $filters = array()) {
		$cond = '';
		if (!empty($filters['cats'])) {
			$cond .= 'AND (1 = 0 ';
			foreach ($filters['cats'] as $cat) {
				$cond .= 'OR shortname = "'.$cat.'" ';
			}
			$cond .= ') ';
		}

		if (empty($filters['published'])) {
			$cond .= 'AND published = 1 ';
		} else if ($filters['published'] != -1) {
			$published = intval($filters['published']);
			if ($published == 0 || $published == 1) {
				$cond .= 'AND published = '.$published.' ';
			}
		}

		if (empty($filters['publish_date'])) {
			$cond .= 'AND publish_date <= NOW()';
		} else if ($filters['publish_date'] != -1) {
			$cond .= 'AND publish_date <= "'.$filters['publish_date'].'"';
		}

		$id_lang = WLang::getLangId();

		if ($order == 'created_date') {
			$order = 'news.created_date';
		}

		$prep = $this->db->prepare('
			SELECT DISTINCT(id), news.*, news_lang.*
			FROM news
			LEFT JOIN news_lang
			ON id = news_lang.id_news AND id_lang = :id_lang
			LEFT JOIN news_cats_relations
			ON id = news_cats_relations.id_news
			LEFT JOIN news_cats
			ON id_cat = cid
			WHERE 1 = 1 '.$cond.'
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();

		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$data['cats'] = $this->getCatsOfNews($data['id']);

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

		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *
			FROM news
			LEFT JOIN news_lang
			ON id = id_news AND id_lang = :id_lang
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		$data = $prep->fetch(PDO::FETCH_ASSOC);

		if (!empty($data)) {
			$data['cats'] = $this->getCatsOfNews($id_news);
		}

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
			SELECT *
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
	 * @param string $shortname
	 * @return array
	 */
	public function getCatByShortname($shortname) {
		if (empty($shortname)) {
			return false;
		}

		$prep = $this->db->prepare('
			SELECT *
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
			SELECT *
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
	public function getCatsStructure($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT *
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

	public function increaseViews($id_news) {
		$prep = $this->db->prepare('
			UPDATE news
			SET views = views + 1
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_news);

		return $prep->execute();
	}
}

?>
