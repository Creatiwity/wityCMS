<?php
/**
 * Page Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'page'.DS.'front'.DS.'model.php';

/**
 * PageAdminModel is the Admin Model of the Page Application
 *
 * @package Apps\Page\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-07-10-2014
 */
class PageAdminModel extends PageModel {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Creates a page in the database.
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function createPage($data) {
		$prep = $this->db->prepare('
			INSERT INTO page(url, title, subtitle, author, content, meta_title, short_title, keywords, description, parent, image)
			VALUES (:url, :title, :subtitle, :author, :content, :meta_title, :short_title, :keywords, :description, :parent, :image)
		');
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':subtitle', $data['subtitle']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':short_title', $data['short_title']);
		$prep->bindParam(':keywords', $data['keywords']);
		$prep->bindParam(':description', $data['description']);
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':image', $data['image']);
		
		if ($prep->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	 * Updates a page in the database from a set of data
	 * 
	 * @param int $page_id
	 * @param array $data
	 * @return bool Success?
	 */
	public function updatePage($page_id, $data) {
		$prep = $this->db->prepare('
			UPDATE page
			SET url = :url, title = :title, subtitle = :subtitle, author = :author, content = :content, meta_title = :meta_title, short_title = :short_title, 
				keywords = :keywords, description = :description, parent = :parent, image = :image
			WHERE id = :id
		');
		$prep->bindParam(':id', $page_id);
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':subtitle', $data['subtitle']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':short_title', $data['short_title']);
		$prep->bindParam(':keywords', $data['keywords']);
		$prep->bindParam(':description', $data['description']);
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':image', $data['image']);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a page in the database
	 * 
	 * @param int $page_id
	 * @return bool Success?
	 */
	public function deletePage($page_id) {
		$prep = $this->db->prepare('
			DELETE FROM page WHERE id = :id
		');
		$prep->bindParam(':id', $page_id, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	public function removeParentPage($parent_id) {
		$prep = $this->db->prepare('
			UPDATE page
			SET parent = TRIM("/" FROM REPLACE(CONCAT("/", parent, "/"), CONCAT("/", :parent_id, "/"), "/"))
		');
		$prep->bindParam(':parent_id', $parent_id);
		$prep->execute();
		
		// Reset parent to 0 when the field is empty
		$prep = $this->db->prepare('
			UPDATE page
			SET parent = 0
			WHERE parent = ""
		');
		$prep->execute();
	}
}

?>
