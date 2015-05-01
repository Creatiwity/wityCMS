<?php
/**
 * News Application - Admin Model
 */

defined('IN_WITY') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'news'.DS.'front'.DS.'model.php';

/**
 * NewsAdminModel is the Admin Model of the News Application
 *
 * @package Apps\News\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-24-03-2015
 */
class NewsAdminModel extends NewsModel {
	public function __construct() {
		parent::__construct();
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
		
		if (!empty($filters['published'])) {
			$published = intval($filters['published']);
			if ($published == 0 || $published == 1) {
				$cond .= 'AND published = '.$published.' ';
			}
		}
		
		$prep = $this->db->prepare('
			SELECT DISTINCT(id), news.*
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
			
			$result[] = $data;
		}
		
		return $result;
	}
	
	/**
	 * Creates a News in the database from a set of data
	 * 
	 * @param array $data
	 * @return mixed ID of the new item or false on error
	 */
	public function createNews($data) {
		$prep = $this->db->prepare('
			INSERT INTO news(url, image, title, author, content, meta_title, meta_description, published, publish_date)
			VALUES (:url, :image, :title, :author, :content, :meta_title, :meta_description, :published, :publish_date)
		');
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':meta_description', $data['meta_description']);
		$prep->bindParam(':published', $data['published']);
		$prep->bindParam(':publish_date', $data['publish_date']);
		
		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	 * Updates a News in the database from a set of data
	 * 
	 * @param int $id_news
	 * @param array $data
	 * @return bool Success?
	 */
	public function updateNews($id_news, $data) {
		$prep = $this->db->prepare('
			UPDATE news
			SET url = :url, image = :image, title = :title, author = :author, content = :content, 
				meta_title = :meta_title, meta_description = :meta_description, published = :published, publish_date = :publish_date
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news);
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':meta_description', $data['meta_description']);
		$prep->bindParam(':published', $data['published']);
		$prep->bindParam(':publish_date', $data['publish_date']);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a News in the database
	 * 
	 * @param int $id_news
	 * @return bool Success?
	 */
	public function deleteNews($id_news) {
		$prep = $this->db->prepare('
			DELETE FROM news WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Create a relation between a News and a Category
	 * 
	 * @param int $id_news
	 * @param int $id_cat
	 * @return bool Success?
	 */
	public function addCatToNews($id_news, $id_cat) {
		$prep = $this->db->prepare('
			INSERT INTO news_cats_relations(id_news, id_cat)
			VALUES (:id_news, :id_cat)
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->bindParam(':id_cat', $id_cat, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Destroy all relations link categories to a given News_ID
	 * 
	 * @param int $id_news
	 * @return bool Success?
	 */
	public function removeCatsFromNews($id_news) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE id_news = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Destroy all relations involving a given News_Cat_ID
	 * 
	 * @param int $id_cat
	 * @return bool Success?
	 */
	public function removeRelationsOfCat($id_cat) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE id_cat = :id_cat
		');
		$prep->bindParam(':id_cat', $id_cat, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Removes all relations to a parent category
	 * 
	 * @param int $parent_id_cat
	 * @return bool Success?
	 */
	public function unlinkChildrenOfParentCat($parent_id_cat) {
		$prep = $this->db->prepare('
			UPDATE news_cats
			SET parent = 0
			WHERE parent = :id_cat
		');
		$prep->bindParam(':id_cat', $parent_id_cat);
		
		return $prep->execute();
	}
	
	/**
	 * Creates a news category in the database
	 * 
	 * @param array $data
	 * @return mixed ID of the new item or false on error
	 */
	public function createCat($data) {
		$prep = $this->db->prepare('
			INSERT INTO news_cats(name, shortname, parent)
			VALUES (:name, :shortname, :parent)
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':shortname', $data['shortname']);
		$prep->bindParam(':parent', $data['parent']);
		
		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	 * Updates a category in the database
	 * 
	 * @param int $id_cat
	 * @param array $data
	 * @return bool Success?
	 */
	public function updateCat($id_cat, $data) {
		$prep = $this->db->prepare('
			UPDATE news_cats
			SET name = :name, shortname = :shortname, parent = :parent
			WHERE cid = :id_cat
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':shortname', $data['shortname']);
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':id_cat', $id_cat);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a news category in the database
	 * 
	 * @param int $id_cat
	 * @return bool Success?
	 */
	public function deleteCat($id_cat) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats WHERE cid = :id_cat
		');
		$prep->bindParam(':id_cat', $id_cat, PDO::PARAM_INT);
		
		return $prep->execute();
	}
}

?>
