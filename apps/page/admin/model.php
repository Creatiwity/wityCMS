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
 * @version 0.6.2-04-06-2018
 */
class PageAdminModel extends PageModel {
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieves all data linked to a page
	 *
	 * @param int $id_page
	 * @return array
	 */
	public function getPage($id_page) {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *
			FROM page
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_page, PDO::PARAM_INT);
		$prep->execute();

		$page = $prep->fetch(PDO::FETCH_ASSOC);

		// Get lang fields
		$prep = $this->db->prepare('
			SELECT *
			FROM page_lang
			WHERE id_page = :id_page
		');
		$prep->bindParam(':id_page', $id_page, PDO::PARAM_INT);
		$prep->execute();

		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			foreach ($data as $key => $value) {
				$page[$key.'_'.$data['id_lang']] = $value;
			}
		}

		return $page;
	}

	/**
	 * Create lang line.
	 *
	 * @param int $id_page
	 * @param array $data_translatable
	 */
	private function insertPageLang($id_page, $data_translatable) {
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
			// Clean previous line
			$prep = $this->db->prepare('DELETE FROM page_lang WHERE id_page = ? AND id_lang = ?');
			$prep->execute(array($id_page, $id_lang));

			$prep = $this->db->prepare('
				INSERT INTO page_lang(id_page, id_lang, title, subtitle, author, content, url, meta_title, meta_description)
				VALUES (:id_page, :id_lang, :title, :subtitle, :author, :content, :url, :meta_title, :meta_description)
			');
			$prep->bindParam(':id_page', $id_page, PDO::PARAM_INT);
			$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
			$prep->bindParam(':title', $values['title']);
			$prep->bindParam(':subtitle', $values['subtitle']);
			$prep->bindParam(':author', $values['author']);
			$prep->bindParam(':content', $values['content']);
			$prep->bindParam(':url', $values['url']);
			$prep->bindParam(':meta_title', $values['meta_title']);
			$prep->bindParam(':meta_description', $values['meta_description']);

			if (!$prep->execute()) {
				$exec = false;
			}
		}

		return $exec;
	}

	/**
	 * Creates a page in the database.
	 *
	 * @param array $data
	 * @param array $data_translatable
	 * @return bool
	 */
	public function createPage($data, $data_translatable) {
		$prep = $this->db->prepare('
			INSERT INTO page(parent, menu, image)
			VALUES (:parent, :menu, :image)
		');
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':menu', $data['menu']);
		$prep->bindParam(':image', $data['image']);

		if (!$prep->execute()) {
			return false;
		}

		$id_page = $this->db->lastInsertId();

		if ($this->insertPageLang($id_page, $data_translatable)) {
			return $id_page;
		} else {
			return false;
		}
	}

	/**
	 * Updates a page in the database from a set of data
	 *
	 * @param int $id_page
	 * @param array $data
	 * @param array $data_translatable
	 * @return bool Success?
	 */
	public function updatePage($id_page, $data, $data_translatable) {
		$prep = $this->db->prepare('
			UPDATE page
			SET parent = :parent, menu = :menu, image = :image
			WHERE id = :id_page
		');
		$prep->bindParam(':id_page', $id_page);
		$prep->bindParam(':parent', $data['parent']);
		$prep->bindParam(':menu', $data['menu']);
		$prep->bindParam(':image', $data['image']);

		if (!$prep->execute()) {
			return false;
		}

		return $this->insertPageLang($id_page, $data_translatable);
	}

	/**
	 * Deletes a page in the database
	 *
	 * @param int $id_page
	 * @return bool Success?
	 */
	public function deletePage($id_page) {
		$prep = $this->db->prepare('
			DELETE FROM page WHERE id = :id
		');
		$prep->bindParam(':id', $id_page, PDO::PARAM_INT);

		$exec1 = $prep->execute();

		$prep = $this->db->prepare('
			DELETE FROM page_lang WHERE id_page = :id_page
		');
		$prep->bindParam(':id_page', $id_page, PDO::PARAM_INT);
		$exec2 = $prep->execute();

		return $exec1 && $exec2;
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
