<?php
/**
 * Team Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'team'.DS.'front'.DS.'model.php';

/**
 * TeamAdminModel is the admin Model of the Team Application.
 *
 * @package Apps\Team\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class TeamAdminModel extends TeamModel {
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieves all data linked to an Expertise
	 *
	 * @param int $id_project
	 * @return array
	 */
	public function getMember($id_member) {
		if (empty($id_member)) {
			return false;
		}

		$prep = $this->db->prepare('
			SELECT *
			FROM team_member
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_member, PDO::PARAM_INT);
		$prep->execute();

		$member = $prep->fetch(PDO::FETCH_ASSOC);

		// Get lang fields
		$prep = $this->db->prepare('
			SELECT *
			FROM team_member_lang
			WHERE id_member = :id_member
		');
		$prep->bindParam(':id_member', $id_member, PDO::PARAM_INT);
		$prep->execute();

		while ($data = $prep->fetch(PDO::FETCH_ASSOC)) {
			foreach ($data as $key => $value) {
				$member[$key.'_'.$data['id_lang']] = $value;
			}
		}

		return $member;
	}

	/**
	 * Defines a config in config table.
	 *
	 * @param string $key
	 * @param string $value
	 * @return Request status
	 */
	public function setConfig($key, $value) {
		$prep = $this->db->prepare('
			UPDATE team_config
			SET `value` = :value
			WHERE `key` = :key
		');
		$prep->bindParam(':key', $key);
		$prep->bindParam(':value', $value);

		return $prep->execute();
	}

	/**
	 * Find overview max position
	 *
	 * @return int
	 */
	public function getNewPosition() {
		$prep = $this->db->prepare('
			SELECT MAX(position)
			FROM team_member
		');
		$prep->execute();

		$position = $prep->fetchColumn();

		return is_null($position) ? 0 : intval($position) + 1;
	}

	/**
	 * Create lang line.
	 *
	 * @param int $id_member
	 * @param array $data_translatable
	 */
	private function insertMemberLang($id_member, $data_translatable) {
		$exec = true;
		foreach ($data_translatable as $id_lang => $values) {
			// Clean previous line
			$prep = $this->db->prepare('DELETE FROM team_member_lang WHERE id_member = ? AND id_lang = ?');
			$prep->execute(array($id_member, $id_lang));

			$prep = $this->db->prepare('
				INSERT INTO team_member_lang(id_member, id_lang, title, description)
				VALUES (:id_member, :id_lang, :title, :description)
			');
			$prep->bindParam(':id_member', $id_member, PDO::PARAM_INT);
			$prep->bindParam(':id_lang', $id_lang, PDO::PARAM_INT);
			$prep->bindParam(':title', $values['title']);
			$prep->bindParam(':description', $values['description']);

			if (!$prep->execute()) {
				$exec = false;
			}
		}

		return $exec;
	}

	/**
	 * Creates a new Member.
	 *
	 * @param string $data Member's data
	 */
	public function createMember($data, $data_translatable) {
		// Get position
		$position = $this->getNewPosition();

		$prep = $this->db->prepare('
			INSERT INTO team_member(name, email, linkedin, twitter, image, image_hover, position)
			VALUES (:name, :email, :linkedin, :twitter, :image, :image_hover,:position)
		');
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':email', $data['email']);
		$prep->bindParam(':linkedin', $data['linkedin']);
		$prep->bindParam(':twitter', $data['twitter']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':image_hover', $data['image_hover']);
		$prep->bindParam(':position', $position);

		if (!$prep->execute()) {
			return false;
		}

		$id_member = $this->db->lastInsertId();

		if ($this->insertMemberLang($id_member, $data_translatable)) {
			return $id_member;
		} else {
			return false;
		}
	}

	/**
	 * Updates a Member.
	 *
	 * @param int $member_id
	 * @param string $data Comment's data
	 */
	public function updateMember($id_member, $data, $data_translatable) {
		if (empty($id_member)) {
			return false;
		}

		$prep = $this->db->prepare('
			UPDATE team_member
			SET name = :name, email = :email,
				linkedin = :linkedin, twitter = :twitter, image = :image, image_hover = :image_hover
			WHERE id = :id
		');
		$prep->bindParam(':id', $id_member, PDO::PARAM_INT);
		$prep->bindParam(':name', $data['name']);
		$prep->bindParam(':email', $data['email']);
		$prep->bindParam(':linkedin', $data['linkedin']);
		$prep->bindParam(':twitter', $data['twitter']);
		$prep->bindParam(':image', $data['image']);
		$prep->bindParam(':image_hover', $data['image_hover']);

		if (!$prep->execute()) {
			return false;
		}

		return $this->insertMemberLang($id_member, $data_translatable);
	}

	public function reorderElement($id, $position) {
		// Set new position
		$prep = $this->db->prepare('
			UPDATE team_member
			SET position = :position
			WHERE id = :id
		');

		$prep->bindParam(':position', $position);
		$prep->bindParam(':id', $id);

		return $prep->execute();
	}

	/**
	 * Deletes a Member.
	 *
	 * @param int $member_id
	 * @param string $data Comment's data
	 */
	public function deleteMember($member_id) {
		$prep = $this->db->prepare('
			DELETE FROM team_member
			WHERE id = :id
		');
		$prep->bindParam(':id', $member_id, PDO::PARAM_INT);
		$exec1 = $prep->execute();

		$prep = $this->db->prepare('
			DELETE FROM team_member_lang
			WHERE id_member = :id_member
		');
		$prep->bindParam(':id_member', $member_id, PDO::PARAM_INT);
		$exec2 = $prep->execute();

		return $exec1 && $exec2;
	}
}

?>
