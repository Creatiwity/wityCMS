<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @auteur	NkDeuS
 * @version	$Id: index.php 0001 05-04-2010 xpLosIve. $
 *
 * Copyright 2010 NkDeuS.Com
 */

class PageAdminModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Récupère la liste complète des pages
	 */
	public function getPageList($from, $number, $order = 'title', $asc = true) {
		$prep = $this->db->prepare('
			SELECT id, url, title, author, content, creation_time, edit_time
			FROM pages
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
			LIMIT :start, :number
		');
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		return $prep->fetchAll();
	}
	
	/**
	 * Vérifie si un permalien n'est pas déjà utilisé
	 */
	public function permalienAvailable($permalien) {
		$prep = $this->db->prepare('
			SELECT * FROM pages WHERE url = :permalien
		');
		$prep->bindParam(':permalien', $permalien);
		$prep->execute();
		return $prep->rowCount() == 0;
	}
	
	public function createPage($data) {
		$prep = $this->db->prepare('
			INSERT INTO pages(url, title, author, content, keywords, creation_time, edit_time)
			VALUES (:permalien, :title, :author, :content, :keywords, :creation_time, :edit_time)
		');
		$prep->bindParam(':permalien', $data['pUrl']);
		$prep->bindParam(':title', $data['pTitle']);
		$prep->bindParam(':author', $data['pAuthor']);
		$prep->bindParam(':content', $data['pContent']);
		$prep->bindParam(':keywords', $data['pKeywords']);
		$prep->bindParam(':creation_time', time());
		$prep->bindParam(':edit_time', time());
		return $prep->execute() or die (var_dump($prep->errorInfo()));
	}
	
	/**
	 * Obtenir le dernier id inséré dans la table
	 * Utile pour le nouveau routage lors de la création d'une page
	 */
	public function getLastId() {
		return intval($this->db->lastInsertId());
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
	
	public function updatePage($id, $data) {
		$string = '';
		foreach ($data as $key => $value) {
			$string .= strtolower(substr($key, 1)).' = '.$this->db->quote($value).', ';
		}
		$string = substr($string, 0, -2);
		
		return $this->db->query('
			UPDATE pages
			SET '.$string.'
			WHERE id = '.$id
		);
	}
	
	public function deletePage($id) {
		$prep = $this->db->prepare('
			DELETE FROM pages WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	public function validId($id) {
		$prep = $this->db->prepare('
			SELECT * FROM pages WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		$prep->execute();
		return $prep->rowCount() == 1;
	}
}

?>