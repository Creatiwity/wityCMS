<?php
/**
 * Page Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageModel is the Front Model of the Page Application
 *
 * @package Apps\Page\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class PageModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare tables
		$this->db->declareTable('page');
		$this->db->declareTable('page_lang');
	}

	/**
	 * Counts pages in the database.
	 *
	 * @return int
	 */
	public function countPages() {
		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM page
		');
		$prep->execute();

		return intval($prep->fetchColumn());
	}

	/**
	 * Checks that a given ID matches a ID in the database
	 *
	 * @param int $id_page
	 * @return bool
	 */
	public function validExistingPageId($id_page) {
		$prep = $this->db->prepare('
			SELECT *
			FROM page
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_page, PDO::PARAM_INT);
		$prep->execute();

		return $prep->rowCount() == 1;
	}

	/**
	 * Retrieves a set of page.
	 *
	 * @param int $from
	 * @param int $number
	 * @param string $order Ordering field name
	 * @param bool $asc true = ASC order / false = DESC order
	 * @return array
	 */
	public function getPages($from = 0, $number = 0, $order = 'virtual_parent', $asc = true) {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *,
				CASE
					WHEN parent = 0 THEN id
					ELSE CONCAT(parent, "/")
				END AS virtual_parent
			FROM page
			LEFT JOIN page_lang
			ON id = id_page AND id_lang = :id_lang
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);

		if ($number > 0) {
			$prep->bindParam(':start', $from, PDO::PARAM_INT);
			$prep->bindParam(':number', $number, PDO::PARAM_INT);
		}

		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		$result = array();
		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			$data['level'] = substr_count($data['parent'], '/');

			if ($data['parent'] != '0') {
				$data['level'] += 1;
			}

			$result[] = $data;
		}

		return $result;
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
			LEFT JOIN page_lang
			ON id = id_page AND id_lang = :id_lang
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_page, PDO::PARAM_INT);
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	public function increaseView($id_page) {
		$prep = $this->db->prepare('
			UPDATE page
			SET views = views + 1
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_page);

		return $prep->execute();
	}

	/**
	 * Transforms a set of pages into a PHP multidimensionnal array.
	 *
	 * @param array $params Set of pages returned by DB
	 * @return array
	 */
	private function formatPages($pages) {
		$result = array();

		// Mapping object to target the parent page
		$map = array();

		foreach ($pages as $data) {
			$id_page = intval($data['id']);

			if (empty($data['parent'])) {
				$result[$id_page] = $data;
				$result[$id_page]['childs'] = array();

				$map[$id_page] = &$result[$id_page];
			} else if (!isset($map[$data['parent']])) {
				$result[$id_page] = $data;
				$result[$id_page]['childs'] = array();

				$map[$data['parent'].'/'.$id_page] = &$result[$id_page];
			} else {
				$map[$data['parent']]['childs'][$id_page] = $data;
				$map[$data['parent']]['childs'][$id_page]['childs'] = array();

				$map[$data['parent'].'/'.$id_page] = &$map[$data['parent']]['childs'][$id_page];
			}
		}

		return $result;
	}

	/**
	 * Retrieves the child pages of $id_parent as a PHP multidimensionnal array.
	 *
	 * @return array
	 */
	public function getChildPages($id_parent) {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *
			FROM page
			LEFT JOIN page_lang
			ON id = id_page AND id_lang = :id_lang
			WHERE id = :parent OR parent LIKE :parent OR parent LIKE :parent_regexp
			ORDER BY parent ASC, id ASC
		');
		$prep->bindParam(':parent', $id_parent);
		$parent = $id_parent.'/%';
		$prep->bindParam(':parent_regexp', $parent);
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		$data = $prep->fetchAll(PDO::FETCH_ASSOC);

		if (sizeof($data) > 1) {
			return $this->formatPages($data);
		} else {
			return array();
		}
	}
}

?>
