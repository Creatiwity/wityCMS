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
 * @version 0.4.0-19-04-2013
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
			INSERT INTO news(url, title, author, content, meta_title, keywords, description)
			VALUES (:url, :title, :author, :content, :meta_title, :keywords, :description)
		');
		$prep->bindParam(':url', $data['news_url']);
		$prep->bindParam(':title', $data['news_title']);
		$prep->bindParam(':author', $data['news_author']);
		$prep->bindParam(':content', $data['news_content']);
		$prep->bindParam(':meta_title', $data['news_meta_title']);
		$prep->bindParam(':keywords', $data['news_keywords']);
		$prep->bindParam(':description', $data['news_description']);
		
		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	 * Updates a News in the database from a set of data
	 * 
	 * @param int $news_id
	 * @param array $data
	 * @return bool Success?
	 */
	public function updateNews($news_id, $data) {
		$prep = $this->db->prepare('
			UPDATE news
			SET url = :url, title = :title, author = :author, content = :content, meta_title = :meta_title, 
				keywords = :keywords, description = :description
			WHERE id = :id
		');
		$prep->bindParam(':id', $news_id);
		$prep->bindParam(':url', $data['news_url']);
		$prep->bindParam(':title', $data['news_title']);
		$prep->bindParam(':author', $data['news_author']);
		$prep->bindParam(':content', $data['news_content']);
		$prep->bindParam(':meta_title', $data['news_meta_title']);
		$prep->bindParam(':keywords', $data['news_keywords']);
		$prep->bindParam(':description', $data['news_description']);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a News in the database
	 * 
	 * @param int $news_id
	 * @return bool Success?
	 */
	public function deleteNews($news_id) {
		$prep = $this->db->prepare('
			DELETE FROM news WHERE id = :news_id
		');
		$prep->bindParam(':news_id', $news_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Create a relation between a News and a Category
	 * 
	 * @param int $news_id
	 * @param int $cat_id
	 * @return bool Success?
	 */
	public function addCatToNews($news_id, $cat_id) {
		$prep = $this->db->prepare('
			INSERT INTO news_cats_relations(news_id, cat_id)
			VALUES (:news_id, :cat_id)
		');
		$prep->bindParam(':news_id', $news_id, PDO::PARAM_INT);
		$prep->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Destroy all relations link categories to a given News_ID
	 * 
	 * @param int $news_id
	 * @return bool Success?
	 */
	public function removeCatsFromNews($news_id) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE news_id = :news_id
		');
		$prep->bindParam(':news_id', $news_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Destroy all relations involving a given News_Cat_ID
	 * 
	 * @param int $cat_id
	 * @return bool Success?
	 */
	public function removeRelationsOfCat($cat_id) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE cat_id = :cat_id
		');
		$prep->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Removes all relations to a parent category
	 * 
	 * @param int $parent_cat_id
	 * @return bool Success?
	 */
	public function unlinkChildrenOfParentCat($parent_cat_id) {
		$prep = $this->db->prepare('
			UPDATE news_cats
			SET parent = 0
			WHERE parent = :cat_id
		');
		$prep->bindParam(':cat_id', $parent_cat_id);
		
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
		$prep->bindParam(':name', $data['news_cat_name']);
		$prep->bindParam(':shortname', $data['news_cat_shortname']);
		$prep->bindParam(':parent', $data['news_cat_parent']);
		
		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	 * Updates a category in the database
	 * 
	 * @param int $cat_id
	 * @param array $data
	 * @return bool Success?
	 */
	public function updateCat($cat_id, $data) {
		$prep = $this->db->prepare('
			UPDATE news_cats
			SET name = :name, shortname = :shortname, parent = :parent
			WHERE cid = :cat_id
		');
		$prep->bindParam(':name', $data['news_cat_name']);
		$prep->bindParam(':shortname', $data['news_cat_shortname']);
		$prep->bindParam(':parent', $data['news_cat_parent']);
		$prep->bindParam(':cat_id', $cat_id);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a news category in the database
	 * 
	 * @param int $cat_id
	 * @return bool Success?
	 */
	public function deleteCat($cat_id) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats WHERE cid = :cat_id
		');
		$prep->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
}

?>
