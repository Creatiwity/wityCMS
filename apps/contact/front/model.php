<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif <Johan Dufau>
 * @version	$Id: apps/contact/front/model.php 0000 19-05-2011 Julien1619 $
 */

class ContactModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Création d'un mail de contact dans la BDD
	 */
	public function createMail($data) {
		$prep = $this->db->prepare('
			INSERT INTO contact_mails(userid, name, organisme, email, objet, message)
			VALUES (:userid, :name, :organisme, :email, :objet, :message)
		');
		$prep->bindParam(':userid', $data['userid'], PDO::PARAM_INT);
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':organisme', $data['organisme']);
		$prep->bindParam(':email', $data['email']);
		$prep->bindParam(':objet', $data['objet']);
		$prep->bindParam(':message', $data['message']);
		return $prep->execute() or die(var_dump($prep->errorInfo()));
	}
}

?>