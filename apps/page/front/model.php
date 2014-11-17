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
 * @version 0.5.0-dev-07-10-2014
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
	}
	
	/**
	 * Counts page in the database
	 * 
	 * @return int
	 */
	public function countPage() {
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
	 * @param int $page_id
	 * @return bool
	 */
	public function validExistingPageId($page_id) {
		$page_id = intval($page_id);
		
		if ($page_id <= 0) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT * FROM page WHERE id = :id
		');
		$prep->bindParam(':id', $page_id, PDO::PARAM_INT);
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
	public function getPageList($from, $number, $order = 'virtual_parent', $asc = false) {
		$prep = $this->db->prepare('
			SELECT id, url, title, subtitle, author, content, meta_title, short_title, keywords, 
				description, views, parent, image, created_date, created_by, modified_date,
				CASE
					WHEN parent = 0 THEN id
					ELSE parent
				END AS virtual_parent
			FROM page
			ORDER BY page.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
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
	
	public function increaseView($page_id) {
		$prep = $this->db->prepare('
			UPDATE page
			SET views = views + 1
			WHERE id = :id
		');
		$prep->bindParam(':id', $page_id);
		
		return $prep->execute();
	}
	
	private function formatPages($pages) {
		$result = array();
		
		// Mapping object to target the parent page
		$map = array();
		
		foreach ($pages as $data) {
			$page_id = intval($data['id']);
			
			if (empty($data['parent'])) {
				$result[$page_id] = $data;
				$result[$page_id]['childs'] = array();
				
				$map[$page_id] = &$result[$page_id];
			} else if (!isset($map[$data['parent']])) {
				$result[$page_id] = $data;
				$result[$page_id]['childs'] = array();
				
				$map[$data['parent'].'/'.$page_id] = &$result[$page_id];
			} else {
				$map[$data['parent']]['childs'][$page_id] = $data;
				$map[$data['parent']]['childs'][$page_id]['childs'] = array();
				
				$map[$data['parent'].'/'.$page_id] = &$map[$data['parent']]['childs'][$page_id];
			}
		}
		
		return $result;
	}
	
	/**
	 * Retrieves the full listing of pages in the database.
	 * 
	 * @return array
	 */
	public function getPages($menu = false) {
		$prep = $this->db->prepare('
			SELECT id, url, title, subtitle, author, content, meta_title, short_title, keywords, description, views, parent, image, created_date, created_by, modified_date, modified_by
			FROM page
			'.($menu ? 'WHERE menu = 1' : '').'
			ORDER BY parent ASC, id ASC
		');
		$prep->execute();
		
		return $this->formatPages($prep->fetchAll(PDO::FETCH_ASSOC));
	}
	
	/**
	 * Retrieves all data linked to a page
	 * 
	 * @param int $page_id
	 * @return array
	 */
	public function getPage($page_id) {
		$prep = $this->db->prepare('
			SELECT id, url, title, subtitle, author, content, meta_title, short_title, keywords, description, views, parent, image, created_date, created_by, modified_date
			FROM page
			WHERE id = :id
		');
		$prep->bindParam(':id', $page_id, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Retrieves the full listing of pages in the database.
	 * 
	 * @return array
	 */
	public function getChildPages($parent_id) {
		$prep = $this->db->prepare('
			SELECT id, url, title, subtitle, author, content, meta_title, short_title, keywords, description, views, parent, image, created_date, created_by, modified_date, modified_by
			FROM page
			WHERE parent LIKE :parent_id OR parent LIKE :parent_regexp OR id = :parent_id
			ORDER BY parent ASC, id ASC
		');
		$prep->bindParam(':parent_id', $parent_id);
		$parent = $parent_id.'/%';
		$prep->bindParam(':parent_regexp', $parent);
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