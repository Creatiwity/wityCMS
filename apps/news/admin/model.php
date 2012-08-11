<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: apps/news/admin/model.php 0003 01-08-2011 Fofif $
 */

class NewsAdminModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Récupère la liste complète des pages
	 */
	public function getNewsList($from, $number, $order = 'title', $asc = true) {
		$prep = $this->db->prepare('
			SELECT id, url, title, author, content, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, 
				DATE_FORMAT(modified, "%d/%m/%Y %H:%i") AS modified, views, editor_id
			FROM news
			ORDER BY news.'.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetchAll();
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
		return $prep->fetchAll();
	}
	
	/**
	 * Création d'une news
	 */
	public function createNews($data) {
		$prep = $this->db->prepare('
			INSERT INTO news(url, title, author, content, keywords, date, editor_id, image)
			VALUES (:permalien, :title, :author, :content, :keywords, NOW(), :editor_id, :image)
		');
		$prep->bindParam(':permalien', $data['nUrl']);
		$prep->bindParam(':title', $data['nTitle']);
		$prep->bindParam(':author', $data['nAuthor']);
		$prep->bindParam(':content', $data['nContent']);
		$prep->bindParam(':keywords', $data['nKeywords']);
		$prep->bindParam(':editor_id', $_SESSION['userid']);
		$prep->bindParam(':image', $data['nImage']);
		return $prep->execute() or die (var_dump($prep->errorInfo()));
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
			SELECT url, title, author, content, keywords, DATE_FORMAT(date, "%d/%m/%Y %H:%i") AS date, DATE_FORMAT(modified, "%d/%m/%Y %H:%i") AS modified, image
			FROM news
			WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetch();
	}
	
	/**
	 * Mise à jour d'une news
	 */
	public function updateNews($id, $data) {
		$string = '';
		foreach ($data as $key => $value) {
			$string .= strtolower(substr($key, 1)).' = '.$this->db->quote($value).', ';
		}
		$string = substr($string, 0, -2);
		
		return $this->db->query('
			UPDATE news
			SET '.$string.',
				modified = NOW()
			WHERE id = '.$id
		);
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
	public function validId($id) {
		$prep = $this->db->prepare('
			SELECT * FROM news WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
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
	 * Récupère la liste complète des pages
	 */
	public function getCatList($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT cid, name, shortname, parent
			FROM news_cats
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC')
		);
		$prep->execute();
		return $prep->fetchAll();
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
		$prep->bindParam(':name', $data['cName']);
		$prep->bindParam(':shortname', $data['cShortname']);
		$prep->bindParam(':parent', $data['cParent']);
		return $prep->execute() or die (var_dump($prep->errorInfo()));
	}
	
	/**
	 * Mise à jour d'une catégorie
	 */
	public function updateCat($cid, $data) {
		return $this->db->query('
			UPDATE news_cats
			SET name = '.$this->db->quote($data['cNameEdit']).', shortname = '.$this->db->quote($data['cShortnameEdit']).', parent = '.intval($data['cParentEdit']).'
			WHERE cid = '.$cid
		);
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
