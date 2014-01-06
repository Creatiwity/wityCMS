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
 * @version 0.4.0-19-04-2013
 */
class NewsModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;
	
	/**
	 * @var News Data Model
	 */
	public $news_data_model = array(
		'toDB' => array(
			'news_id' => 'id',
			'news_url' => 'url',
			'news_title' => 'title',
			'news_author' => 'author',
			'news_content' => 'content',
			'news_meta_title' => 'meta_title',
			'news_keywords' => 'keywords',
			'news_description' => 'description',
			'news_date' => 'created_date',
			'news_modified' => 'modified_date',
			'news_editor_id' => 'modified_by',
			'news_views' => 'views',
			'news_publish' => 'published',
			'news_cats' => 'cats'
		),
		'fromDB' => array(
			'id' => 'news_id',
			'url' => 'news_url',
			'title' => 'news_title',
			'author' => 'news_author',
			'content' => 'news_content',
			'meta_title' => 'news_meta_title',
			'keywords' => 'news_keywords',
			'description' => 'news_description',
			'created_date' => 'news_date',
			'modified_date' => 'news_modified',
			'modified_by' => 'news_editor_id',
			'views' => 'news_views',
			'published' => 'news_publish',
			'cats' => 'news_cats'
		)
	);
	
	/**
	 * @var News_cats Data Model
	 */
	public $cats_data_model = array(
		'toDB' => array(
			'news_cat_id' => 'cid',
			'news_cat_name' => 'name',
			'news_cat_shortname' => 'shortname',
			'news_cat_parent' => 'parent',
			'news_cat_parent_name' => 'parent_name'
		),
		'fromDB' => array(
			'cid' => 'news_cat_id',
			'name' => 'news_cat_name',
			'shortname' => 'news_cat_shortname',
			'parent' => 'news_cat_parent',
			'parent_name' => 'news_cat_parent_name'
		)
	);
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		// Declare table
		$this->db->declareTable('news');
		$this->db->declareTable('news_cats');
		$this->db->declareTable('news_cats_relations');
	}
	
	/**
	 * Rename News fields retrieved from DB to model structure (by ref)
	 */
	public function renameNewsFieldsFromDb(&$data) {
		foreach ($data as $prop => $value) {
			if (!empty($this->news_data_model['fromDB'][$prop])) {
				unset($data[$prop]);
				$data[$this->news_data_model['fromDB'][$prop]] = $value;
			}
		}
	}
	
	/**
	 * Rename News_cats fields retrieved from DB to model structure (by ref)
	 */
	public function renameCatsFieldsFromDb(&$data) {
		foreach ($data as $prop => $value) {
			if (!empty($this->cats_data_model['fromDB'][$prop])) {
				unset($data[$prop]);
				$data[$this->cats_data_model['fromDB'][$prop]] = $value;
			}
		}
	}
	
	/**
	 * Counts news in the database
	 * 
	 * @return int
	 */
	public function countNews() {
		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM news
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
	public function getNewsList($from, $number, $order = 'news_date', $asc = false, array $filters = array()) {
		$order = $this->news_data_model['toDB'][$order];
		$cond = '';
		if (!empty($filters)) {
			if (!empty($filters['cats'])) {
				$cond .= '(';
				foreach ($filters['cats'] as $cat) {
					$cond .= 'shortname = "'.$cat.'" OR ';
				}
				$cond = sustr($cond, 0, -4).')';
			}
			
			if (!empty($filters['published'])) {
				if (!empty($cond)) {
					$cond .= ' AND ';
				}
				$cond .= 'published = '.intval($filters['published']);
			}
			
			if (!empty($cond)) {
				$cond = 'WHERE '.$cond;
			}
		}
		
		$prep = $this->db->prepare('
			SELECT DISTINCT(id), url, title, author, content, meta_title, keywords, description, views, published,
				news.created_date, news.modified_date, news.modified_by
			FROM news
			LEFT JOIN news_cats_relations
			ON id = news_id
			LEFT JOIN news_cats
			ON cat_id = cid
			'.$cond.'
			ORDER BY news.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		
		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$data['cats'] = $this->getCatsOfNews($data['id']);
			$this->renameNewsFieldsFromDb($data);
			$result[] = $data;
		}
		
		return $result;
	}
	
	/**
	 * Retrieves all data linked to a News
	 * 
	 * @param int $news_id
	 * @return array
	 */
	public function getNews($news_id) {
		if (empty($news_id)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT id, url, title, author, content, meta_title, keywords, description, views, published,
				created_date, modified_date, modified_by
			FROM news
			WHERE id = :news_id
		');
		$prep->bindParam(':news_id', $news_id, PDO::PARAM_INT);
		$prep->execute();
		
		$data = $prep->fetch(PDO::FETCH_ASSOC);
		
		if (!empty($data)) {
			$data['cats'] = $this->getCatsOfNews($news_id);
			$this->renameNewsFieldsFromDb($data);
		}
		
		return $data;
	}
	
	/**
	 * Retrieves a category from the database
	 * 
	 * @param int $cat_id
	 * @return array
	 */
	public function getCat($cat_id) {
		if (empty($cat_id)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT cid, name, shortname
			FROM news_cats 
			WHERE cid = :cat_id
		');
		$prep->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves a complete set of Categories linked to News
	 * 
	 * @param int $news_id
	 * @return array
	 */
	public function getCatsOfNews($news_id) {
		$prep = $this->db->prepare('
			SELECT cid, name, shortname
			FROM news_cats_relations
			LEFT JOIN news_cats
			ON cat_id = cid
			WHERE news_id = :news_id
		');
		$prep->bindParam(':news_id', $news_id, PDO::PARAM_INT);
		$prep->execute();
		
		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$this->renameCatsFieldsFromDb($data);
			$result[] = $data;
		}
		
		return $result;
	}
	
	/**
	 * Retrieves full set of Categories in the database
	 * 
	 * @param string $order
	 * @param bool $asc
	 * @return array
	 */
	public function getCatsList($order = 'news_cat_name', $asc = true) {
		$order = $this->cats_data_model['toDB'][$order];
		$prep = $this->db->prepare('
			SELECT cid, name, shortname, parent
			FROM news_cats
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();
		
		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$this->renameCatsFieldsFromDb($data);
			$result[] = $data;
		}
		
		// Find parent categories' name
		foreach ($result as $key => $cat) {
			$result[$key]['news_cat_parent_name'] = "";
			if ($cat['news_cat_parent'] != 0) {
				foreach ($result as $key2 => $cat2) {
					if ($cat2['news_cat_id'] == $cat['news_cat_parent']) {
						$result[$key]['news_cat_parent_name'] = $cat2['news_cat_name'];
						break;
					}
				}
			}
		}
		
		return $result;
	}
}

?>