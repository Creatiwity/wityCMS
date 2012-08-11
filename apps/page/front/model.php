<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: model.php 0001 10-04-2011 Fofif $
 */

class PageModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	public function validId($id) {
		$prep = $this->db->prepare('
			SELECT * FROM pages WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
	
	/**
	 * Récupération des données d'une page
	 */
	public function loadPage($id) {
		$prep = $this->db->prepare('
			SELECT url, title, author, content, keywords, creation_time, edit_time
			FROM pages
			WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetch();
	}
}

?>