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
 * @version 1.0.0-07-02-2015
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
	
	public function insertSlide($data, $data_translatable) {
		$prep = $this->db->prepare('
			INSERT INTO `slideshow_slide`(`image`, `url`)
			VALUES(:image, :url)
		');
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':url', $data['url']);
		
		if (!$prep->execute()) {
			return false;
		}
		
		$id_slide = $this->db->lastInsertId();
		
		// Create language lines
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
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
		
		if ($exec) {
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
		
		// Create language lines
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
			$prep = $this->db->prepare('
				UPDATE `slideshow_slide_lang`
				SET `title` = :title, `legend` = :legend
				WHERE `id_slide` = :id_slide AND `id_lang` = :id_lang
			');
			$prep->bindParam(':title', $values['title']);
			$prep->bindParam(':legend', $values['legend']);
			$prep->bindParam(':id_slide', $id_slide, PDO::PARAM_INT);
			$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
			
			if (!$prep->execute()) {
				$exec = false;
			}
		}
		
		return $exec;
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
