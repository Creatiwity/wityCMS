<?php
/**
 * News Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

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
 * @version 0.6.2-04-06-2018
 */
class NewsAdminModel extends NewsModel {
	public function __construct() {
		parent::__construct();
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
			SELECT *
			FROM news
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->execute();

		$news = $prep->fetch(PDO::FETCH_ASSOC);

		if (!empty($news)) {
			$news['cats'] = $this->getCatsOfNews($id_news);
		}

		// Get lang fields
		$prep = $this->db->prepare('
			SELECT *
			FROM news_lang
			WHERE id_news = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$prep->execute();

		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			foreach ($data as $key => $value) {
				$news[$key.'_'.$data['id_lang']] = $value;
			}
		}

		return $news;
	}

	/**
	 * Create lang line.
	 *
	 * @param int $id_news
	 * @param array $data_translatable
	 */
	private function insertNewsLang($id_news, $data_translatable) {
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
			// Clean previous line
			$prep = $this->db->prepare('DELETE FROM news_lang WHERE id_news = ? AND id_lang = ?');
			$prep->execute(array($id_news, $id_lang));

			$prep = $this->db->prepare('
				INSERT INTO news_lang(id_news, id_lang, title, author, content, url, meta_title, meta_description, published, publish_date)
				VALUES (:id_news, :id_lang, :title, :author, :content, :url, :meta_title, :meta_description, :published, :publish_date)
			');
			$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
			$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
			$prep->bindParam(':title', $values['title']);
			$prep->bindParam(':author', $values['author']);
			$prep->bindParam(':content', $values['content']);
			$prep->bindParam(':url', $values['url']);
			$prep->bindParam(':meta_title', $values['meta_title']);
			$prep->bindParam(':meta_description', $values['meta_description']);
			$prep->bindParam(':published', $values['published']);
			$prep->bindParam(':publish_date', $values['publish_date']);

			if (!$prep->execute()) {
				$exec = false;
			}
		}

		return $exec;
	}

	/**
	 * Creates a News in the database from a set of data
	 *
	 * @param array $data
	 * @param array $data_translatable
	 * @return mixed ID of the new item or false on error
	 */
	public function createNews($data, $data_translatable) {
		$prep = $this->db->prepare('
			INSERT INTO news(image)
			VALUES (:image)
		');
		$prep->bindParam(':image', $data['image']);

		if (!$prep->execute()) {
			return false;
		}

		$id_news = $this->db->lastInsertId();

		if ($this->insertNewsLang($id_news, $data_translatable)) {
			return $id_news;
		} else {
			return false;
		}
	}

	/**
	 * Updates a News in the database from a set of data
	 *
	 * @param int $id_news
	 * @param array $data
	 * @param array $data_translatable
	 * @return bool Success?
	 */
	public function updateNews($id_news, $data, $data_translatable) {
		$prep = $this->db->prepare('
			UPDATE news
			SET image = :image
			WHERE id = :id_news
		');
		$prep->bindParam(':id_news', $id_news);
		$prep->bindParam(':image', $data['image']);

		if (!$prep->execute()) {
			return false;
		}

		return $this->insertNewsLang($id_news, $data_translatable);
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
		$exec1 = $prep->execute();

		$prep = $this->db->prepare('
			DELETE FROM news_lang WHERE id_news = :id_news
		');
		$prep->bindParam(':id_news', $id_news, PDO::PARAM_INT);
		$exec2 = $prep->execute();

		return $exec1 && $exec2;
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
