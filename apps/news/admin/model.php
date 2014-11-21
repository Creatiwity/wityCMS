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
 * @version 0.5.0-dev-19-04-2013
 */
class NewsAdminModel extends NewsModel {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Creates a News in the database from a set of data
	 * 
	 * @param array $data
	 * @return mixed ID of the new item or false on error
	 */
	public function createNews($data) {
		$prep = $this->db->prepare('
			INSERT INTO news(url, image, title, author, content, meta_title, keywords, description, published)
			VALUES (:url, :image, :title, :author, :content, :meta_title, :keywords, :description, :published)
		');
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':keywords', $data['keywords']);
		$prep->bindParam(':description', $data['description']);
		$prep->bindParam(':published', $data['published']);
		
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
				meta_title = :meta_title, keywords = :keywords, description = :description, published = :published
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news);
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':keywords', $data['keywords']);
		$prep->bindParam(':description', $data['description']);
		$prep->bindParam(':published', $data['published']);
		
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
