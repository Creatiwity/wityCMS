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
 * @version 0.5.0-dev-23-03-2015
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
			INSERT INTO page(url, title, subtitle, author, content, meta_title, meta_description, parent, image)
			VALUES (:url, :title, :subtitle, :author, :content, :meta_title, :meta_description, :parent, :image)
		');
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':subtitle', $data['subtitle']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':meta_description', $data['meta_description']);
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
	 * @param int $id_page
	 * @param array $data
	 * @return bool
	 */
	public function updatePage($id_page, $data) {
		$prep = $this->db->prepare('
			UPDATE page
			SET url = :url, title = :title, subtitle = :subtitle, author = :author, content = :content, meta_title = :meta_title, 
				meta_description = :meta_description, parent = :parent, image = :image
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_page);
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':title', $data['title']);
		$prep->bindParam(':subtitle', $data['subtitle']);
		$prep->bindParam(':author', $data['author']);
		$prep->bindParam(':content', $data['content']);
		$prep->bindParam(':meta_title', $data['meta_title']);
		$prep->bindParam(':meta_description', $data['meta_description']);
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':image', $data['image']);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes a page in the database
	 * 
	 * @param int $id_page
	 * @return bool
	 */
	public function deletePage($id_page) {
		$prep = $this->db->prepare('
			DELETE FROM page WHERE id = :id
		');
		$prep->bindParam(':id', $id_page, PDO::PARAM_INT);
		
		return $prep->execute();
	}
	
	/**
	 * Deletes parent of a page.
	 * 
	 * @param int $id_parent
	 * @return bool
	 */
	public function removeParentPage($id_parent) {
		$prep = $this->db->prepare('
			UPDATE page
			SET parent = TRIM("/" FROM REPLACE(CONCAT("/", parent, "/"), CONCAT("/", :id_parent, "/"), "/"))
		');
		$prep->bindParam(':id_parent', $id_parent);
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
