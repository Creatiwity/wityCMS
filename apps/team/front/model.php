<?php
/**
 * Team Application - Front Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * TeamModel is the Front Model of the Team Application
 *
 * @package Apps\Team\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class TeamModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('team_member');
		$this->db->declareTable('team_member_lang');
	}

	/**
	 * Get member for a given ID.
	 *
	 * @param int $id_member
	 * @return array|false
	 */
	public function getMember($id_member) {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *
			FROM team_member
			LEFT JOIN team_member_lang
			ON id = id_member AND id_lang = :id_lang
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_member, PDO::PARAM_INT);
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Get the Members.
	 *
	 * @return array
	 */
	public function getMembers() {
		$id_lang = WLang::getLangId();

		$prep = $this->db->prepare('
			SELECT *
			FROM team_member
			LEFT JOIN team_member_lang
			ON id = id_member AND id_lang = :id_lang
			ORDER BY position ASC
		');
		$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
		$prep->execute();

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
}

?>
