<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif
 * @version	$Id: apps/user/admin/model.php 0003 22-08-2011 Fofif $
 */

// Inclusion du modèle principal pour héritage
if (!class_exists('UserModel')) {
	include APP_DIR.'user'.DS.'front'.DS.'model.php';
}

class UserAdminModel extends UserModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
		parent::__construct($this->db);
	}
	
	public function deleteUser($id) {
		$prep = $this->db->prepare('
			DELETE FROM users WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
	
	/**
	 * Récupère la liste complète des pages
	 */
	public function getCatList($order = 'name', $asc = true) {
		$prep = $this->db->prepare('
			SELECT id, name, access
			FROM users_cats
			ORDER BY '.$order.' '.($asc ? 'ASC' : 'DESC').'
		');
		$prep->execute();
		return $prep->fetchAll();
	}
	
	public function createCat($data) {
		$prep = $this->db->prepare('
			INSERT INTO users_cats(name, access)
			VALUES (:name, :access)
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':access', $data['access']);
		return $prep->execute() or die (var_dump($prep->errorInfo()));
	}
	
	public function updateCat($id, $data) {
		return $this->db->query('
			UPDATE users_cats
			SET name = '.$this->db->quote($data['nameEdit']).', access = '.$this->db->quote($data['accessEdit']).'
			WHERE id = '.$id
		);
	}
	
	public function deleteCat($id) {
		$prep = $this->db->prepare('
			DELETE FROM users_cats WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);
		return $prep->execute();
	}
}

?>