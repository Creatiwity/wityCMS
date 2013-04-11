<?php
/**
 * News Application - Admin Model - /apps/news/admin/model.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminModel is the Admin Model of the News Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-11-04-2013
 */
class NewsAdminModel {
	private $db;
	
	public $news_data_model = array();
	public $cats_data_model = array();
	
	public function __construct() {
		$this->db = WSystem::getDB();
		
		$this->news_data_model = array(
			'toDB' => array(
				'news_id' => 'id',
				'news_url' => 'url',
				'news_title' => 'title',
				'news_author' => 'author',
				'news_content' => 'content',
				'news_keywords' => 'keywords',
				'news_date' => 'date',
				'news_modified' => 'modified',
				'news_views' => 'views',
				'news_cats' => 'cat',
				'news_editor_id' => 'editor_id',
				'news_image' => 'image'
			),
			'fromDB' => array(
				'id' => 'news_id',
				'url' => 'news_url',
				'title' => 'news_title',
				'author' => 'news_author',
				'content' => 'news_content',
				'keywords' => 'news_keywords',
				'date' => 'news_date',
				'modified' => 'news_modified',
				'views' => 'news_views',
				'cat' => 'news_cats',
				'editor_id' => 'news_editor_id',
				'image' => 'news_image'
			)
		);

		$this->cats_data_model = array(
			'toDB' => array(
				'news_cat_id' => 'cid',
				'news_cat_name' => 'name',
				'news_cat_shortname' => 'shortname',
				'news_cat_parent' => 'parent',
				'news_cat_parent_name' => 'parent_name'
			),
			'fromDB' => array(
				'cid' => 'news_cat_id',
				'name' => 'news_cat_name',
				'shortname' => 'news_cat_shortname',
				'parent' => 'news_cat_parent',
				'parent_name' => 'news_cat_parent_name'
			)
		);
	}
	
	/**
	 * Récupère la liste complète des pages
	 */
	public function getNewsList($from, $number, $order = 'news_title', $asc = true) {
		$order = $this->news_data_model['toDB'][$order];
		$prep = $this->db->prepare('
			SELECT id, url, title, author, content, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, 
				DATE_FORMAT(modified, "%d/%m/%Y %H:%i") AS modified, views, editor_id
			FROM news
			ORDER BY news.' . $order . ' ' . ($asc ? 'ASC' : 'DESC') . '
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		$result = $prep->fetchAll();
		
		foreach ($result as $key => $news) {
			$result[$key][$this->news_data_model['fromDB']['cat']] = $this->findNewsCats($news['id']);
			
			foreach ($news as $prop => $value) {
				if (!empty($this->news_data_model['fromDB'][$prop])) {
					unset($result[$key][$prop]);
					$result[$key][$this->news_data_model['fromDB'][$prop]] = $value;
				}
			}
		}
		return $result;
	}
	
	public function countNews() {
		$prep = $this->db->prepare('
			SELECT COUNT(*)
			FROM news
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	/**
	 * Récupère les catégories liées à une news
	 */
	public function findNewsCats($nid) {
		$prep = $this->db->prepare('
			SELECT cat_id, name
			FROM news_cats_relations
			LEFT JOIN news_cats
			ON cat_id = cid
			WHERE news_id = :nid
		');
		$prep->bindParam(':nid', $nid, PDO::PARAM_INT);
		$prep->execute();
		$result = $prep->fetchAll();

		foreach ($result as $key => $cat) {                        
			foreach ($cat as $prop => $value) {
				if (!empty($this->cats_data_model['fromDB'][$prop])) {
					unset($result[$key][$prop]);
					$result[$key][$this->cats_data_model['fromDB'][$prop]] = $value;
				}
			}
		}
		return $result;
	}
	
	/**
	 * Création d'une news
	 */
	public function createNews($data) {
		$prep = $this->db->prepare('
			INSERT INTO news(url, title, author, content, keywords, date, image)
			VALUES (:url, :title, :author, :content, :keywords, NOW(), :image)
		');
		$prep->bindParam(':url', $data[$this->news_data_model['fromDB']['url']]);
		$prep->bindParam(':title', $data[$this->news_data_model['fromDB']['title']]);
		$prep->bindParam(':author', $data[$this->news_data_model['fromDB']['author']]);
		$prep->bindParam(':content', $data[$this->news_data_model['fromDB']['content']]);
		$prep->bindParam(':keywords', $data[$this->news_data_model['fromDB']['keywords']]);
		$prep->bindParam(':image', $data[$this->news_data_model['fromDB']['image']]);
		return $prep->execute();
	}
	
	/**
	 * Obtenir le dernier id inséré dans la table
	 * Utile pour le nouveau routage lors de la création d'une page
	 */
	public function getLastNewsId() {
		$prep = $this->db->prepare('
			SELECT id FROM news ORDER BY id DESC LIMIT 1
		');
		$prep->execute();
		return intval($prep->fetchColumn());
	}
	
	/**
	 * Récupération des données d'une news
	 */
	public function loadNews($id) {
		$prep = $this->db->prepare('
			SELECT id, url, title, author, content, keywords, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, DATE_FORMAT(modified, "%d/%m/%Y %H:%i") AS modified, image
			FROM news
			WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		$result = $prep->fetch();

		foreach ($result as $key => $val) {
			if (!empty($this->news_data_model['fromDB'][$key])) {
				unset($result[$key]);
				$result[$this->news_data_model['fromDB'][$key]] = $val;
			}
		}
		return $result;
	}
	
	/**
	 * Mise à jour d'une news
	 */
	public function updateNews($data) {
		$prep = $this->db->prepare('
			UPDATE news
			SET url = :url, title = :title, content = :content, keywords = :keywords, modified = NOW(), editor_id = :editor_id, image = :image 
			WHERE id = :id
		');
		$prep->bindParam(':id', $data[$this->news_data_model['fromDB']['id']]);
		$prep->bindParam(':url', $data[$this->news_data_model['fromDB']['url']]);
		$prep->bindParam(':title', $data[$this->news_data_model['fromDB']['title']]);
		$prep->bindParam(':content', $data[$this->news_data_model['fromDB']['content']]);
		$prep->bindParam(':keywords', $data[$this->news_data_model['fromDB']['keywords']]);
		$prep->bindParam(':editor_id', $_SESSION[$this->news_data_model['fromDB']['editor_id']]);
		$prep->bindParam(':image', $data[$this->news_data_model['fromDB']['image']]);
		return $prep->execute();
	}
	
	/**
	 * Suppression d'une news
	 */
	public function deleteNews($id) {
		$prep = $this->db->prepare('
			DELETE FROM news WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Vérifie l'existence d'une news pour un id donné
	 */
	public function validExistingNewsId($id) {
		if(empty($id)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT * FROM news WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Vérifie l'existence d'une catégorie pour un id donné
	 */
	public function validExistingCatId($cid) {
		if(empty($cid)) {
			return false;
		}
		
		$prep = $this->db->prepare('
			SELECT * FROM news_cats WHERE cid = :cid
		');
		$prep->bindParam(':cid', $cid, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Ajout d'une relation news/cat
	 */
	public function newsAddCat($nid, $cid) {
		$prep = $this->db->prepare('
			INSERT INTO news_cats_relations(news_id, cat_id)
			VALUES (:nid, :cid)
		');
		$prep->bindParam(':nid', $nid, PDO::PARAM_INT);
		$prep->bindParam(':cid', $cid, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Destruction des relations news/cats d'une news
	 */
	public function newsDestroyCats($nid) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE news_id = :nid
		');
		$prep->bindParam(':nid', $nid, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Destruction des relations news/cats d'une catégorie
	 */
	public function catsDestroyNews($cid) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats_relations WHERE cat_id = :cid
		');
		$prep->bindParam(':cid', $cid, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Défait les liens enfants->parents pour un parent donné
	 */
	public function unlinkChildren($cid) {
		$prep = $this->db->prepare('
			UPDATE news_cats
                        SET parent = 0
			WHERE parent = :cid
		');
		$prep->bindParam(':cid', $cid);
		return $prep->execute();
	}
	
	/**
	 * Récupère la liste complète des catégories
	 */
	public function getCatList($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT cid, name, shortname, parent
			FROM news_cats
			ORDER BY ' . $order . ' ' . ($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();                
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($result as $key => $cat) {
			if ($cat['parent'] != 0) {
				foreach ($result as $k2 => $c2) {
					if($c2['cid'] == $cat['parent']) {
						$result[$key]['parent_name'] = $c2['name'];
						break;
					}
				}
			} else {
				$result[$key]['parent_name'] = "";
			}
		}
		
		foreach ($result as $key => $cat) {
			foreach ($cat as $prop => $value) {
				if (!empty($this->cats_data_model['fromDB'][$prop])) {
					unset($result[$key][$prop]);
					$result[$key][$this->cats_data_model['fromDB'][$prop]] = $value;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Vérifie l'existence d'une catégorie
	 */
	public function catExists($id) {
		$prep = $this->db->prepare('
			SELECT * FROM news_cats WHERE cid = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Création d'une catégorie
	 */
	public function createCat($data) {
		$prep = $this->db->prepare('
			INSERT INTO news_cats(name, shortname, parent)
			VALUES (:name, :shortname, :parent)
		');
		$prep->bindParam(':name', $data[$this->cats_data_model['fromDB']['name']]);
		$prep->bindParam(':shortname', $data[$this->cats_data_model['fromDB']['shortname']]);
		$prep->bindParam(':parent', $data[$this->cats_data_model['fromDB']['parent']]);
		return $prep->execute();
	}
	
	/**
	 * Mise à jour d'une catégorie
	 */
	public function updateCat($data) {                
		$prep = $this->db->prepare('
			UPDATE news_cats
			SET name = :name, shortname = :shortname, parent = :parent
			WHERE cid = :cid
		');
		$prep->bindParam(':name', $data[$this->cats_data_model['fromDB']['name']]);
		$prep->bindParam(':shortname', $data[$this->cats_data_model['fromDB']['shortname']]);
		$prep->bindParam(':parent', $data[$this->cats_data_model['fromDB']['parent']]);
		$prep->bindParam(':cid', $data[$this->cats_data_model['fromDB']['cid']]);
		return $prep->execute();
	}
	
	/**
	 * Suppression d'une catégorie
	 */
	public function deleteCat($id) {
		$prep = $this->db->prepare('
			DELETE FROM news_cats WHERE cid = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
}

?>
