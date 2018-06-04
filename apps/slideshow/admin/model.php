<?php
/**
 * Slideshow Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

include_once APPS_DIR.'slideshow'.DS.'front'.DS.'model.php';

/**
 * SlideshowAdminModel is the Admin Model of the Slideshow Application
 *
 * @package Apps\Slideshow\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class SlideshowAdminModel extends SlideshowModel {
	public function getSlide($id_slide) {
		if (empty($id_slide)) {
			return array();
		}

		$prep = $this->db->prepare('
			SELECT *
			FROM `slideshow_slide`
			WHERE `id` = :id_slide
		');
		$prep->bindParam(':id_slide', $id_slide);
		$prep->execute();

		$slide = $prep->fetch(PDO::FETCH_ASSOC);

		// Get lang fields
		$prep = $this->db->prepare('
			SELECT *
			FROM `slideshow_slide_lang`
			WHERE `id_slide` = :id_slide
		');
		$prep->bindParam(':id_slide', $id_slide, PDO::PARAM_INT);
		$prep->execute();

		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			foreach ($data as $key => $value) {
				$slide[$key.'_'.$data['id_lang']] = $value;
			}
		}

		return $slide;
	}

	/**
	 * Find overview max position
	 *
	 * @return int
	 */
	public function getNewSlidePosition() {
		$prep = $this->db->prepare('
			SELECT MAX(`position`)
			FROM `slideshow_slide`
		');
		$prep->execute();

		$position = $prep->fetchColumn();

		return is_null($position) ? 0 : intval($position) + 1;
	}

	/**
	 * Create lang line.
	 *
	 * @param int $id_slide
	 * @param array $data_translatable
	 */
	private function insertSlideLang($id_slide, $data_translatable) {
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
			// Clean previous line
			$prep = $this->db->prepare('DELETE FROM slideshow_slide_lang WHERE id_slide = ? AND id_lang = ?');
			$prep->execute(array($id_slide, $id_lang));

			$prep = $this->db->prepare('
				INSERT INTO `slideshow_slide_lang`(`id_slide`, `id_lang`, `title`, `legend`)
				VALUES (:id_slide, :id_lang, :title, :legend)
			');
			$prep->bindParam(':id_slide', $id_slide, PDO::PARAM_INT);
			$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
			$prep->bindParam(':title', $values['title']);
			$prep->bindParam(':legend', $values['legend']);

			if (!$prep->execute()) {
				$exec = false;
			}
		}

		return $exec;
	}

	public function insertSlide($data, $data_translatable) {
		// Get position
		$position = $this->getNewSlidePosition();

		$prep = $this->db->prepare('
			INSERT INTO `slideshow_slide`(`image`, `url`, `position`)
			VALUES(:image, :url, :position)
		');
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':url', $data['url']);
		$prep->bindParam(':position', $position);

		if (!$prep->execute()) {
			return false;
		}

		$id_slide = $this->db->lastInsertId();

		if ($this->insertSlideLang($id_slide, $data_translatable)) {
			return $id_slide;
		} else {
			return false;
		}
	}

	public function updateSlide($id_slide, $data, $data_translatable) {
		if (empty($id_slide)) {
			return false;
		}

		$prep = $this->db->prepare('
			UPDATE `slideshow_slide`
			SET `image` = :image, `url` = :url
			WHERE `id` = :id
		');
		$prep->bindParam(':id', $id_slide, PDO::PARAM_INT);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':url', $data['url']);

		if (!$prep->execute()) {
			return false;
		}

		return $this->insertSlideLang($id_slide, $data_translatable);
	}

	public function deleteSlide($id_slide) {
		if (empty($id_slide)) {
			return false;
		}

		$prep = $this->db->prepare('
			DELETE FROM `slideshow_slide`
			WHERE `id` = :id
		');
		$prep->bindParam(':id', $id_slide, PDO::PARAM_INT);

		$exec1 = $prep->execute();

		$prep = $this->db->prepare('
			DELETE FROM `slideshow_slide_lang`
			WHERE `id_slide` = :id_slide
		');
		$prep->bindParam(':id_slide', $id_slide, PDO::PARAM_INT);
		$exec2 = $prep->execute();

		return $exec1 && $exec2;
	}

	public function reorderSlide($id, $position) {
		// Set new position
		$prep = $this->db->prepare('
			UPDATE `slideshow_slide`
			SET `position` = :position
			WHERE `id` = :id
		');

		$prep->bindParam(':position', $position);
		$prep->bindParam(':id', $id);

		return $prep->execute();
	}

	/**
	 * Defines a config in config table.
	 *
	 * @param string $key
	 * @param string $value
	 * @return Request status
	 */
	public function setConfig($key, $value) {
		$prep = $this->db->prepare('
			UPDATE `slideshow_config`
			SET `value` = :value
			WHERE `key` = :key
		');
		$prep->bindParam(':key', $key);
		$prep->bindParam(':value', $value);

		return $prep->execute();
	}
}

?>
