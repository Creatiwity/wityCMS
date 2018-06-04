<?php
/**
 * Slideshow Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SlideshowModel is the Front Model of the Slideshow Application
 *
 * @package Apps\Slideshow\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class SlideshowModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('slideshow_slide');
		$this->db->declareTable('slideshow_slide_lang');
		$this->db->declareTable('slideshow_config');
	}

	/**
	 * Checks that a given ID matches with a Slide ID in the database.
	 *
	 * @param int $partner_id
	 * @return bool
	 */
	public function validSlideId($id_slide) {
		$id_slide = intval($id_slide);

		if (empty($id_slide)) {
			return false;
		}

		$prep = $this->db->prepare('
			SELECT *
			FROM `slideshow_slide`
			WHERE `id` = :id_slide
		');
		$prep->bindParam(':id_slide', $id_slide, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}

	public function getSlides() {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT s.*, sl.`title`, sl.`legend`
			FROM `slideshow_slide` s
			LEFT JOIN `slideshow_slide_lang` sl
			ON `id` = id_slide AND `id_lang` = :id_lang
			ORDER BY s.`position` ASC
		');
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSlide($id_slide) {
		if (empty($id_slide)) {
			return false;
		}

		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT s.*, sl.`title`, sl.`legend`
			FROM `slideshow_slide` s
			LEFT JOIN `slideshow_slide_lang` sl
			ON `id` = id_slide AND `id_lang` = :id_lang
			WHERE `id` = :id_slide
		');
		$prep->bindParam(':id_slide', $id_slide);
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves the configuration of the app.
	 *
	 * @return array
	 */
	public function getConfig() {
		$prep = $this->db->prepare('
			SELECT `key`, `value`
			FROM `slideshow_config`
		');
		$prep->execute();

		$config = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$config[$data['key']] = $data['value'];
		}

		return $config;
	}
}

?>
