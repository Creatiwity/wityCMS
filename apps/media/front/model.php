<?php
/**
 * Media Application - Front Model - /apps/media/front/model.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * MediaModel is the Front Model of the Media Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class MediaModel {
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('media_access_history');
		$this->db->declareTable('media_filetag_rel');
		$this->db->declareTable('media_list');
		$this->db->declareTable('media_tags');
	}

	public function fileIDExists($fileID) {
		$prep = $this->db->prepare('
			SELECT COUNT(id)
			FROM media_list
			WHERE fileID = :fileID
		');
		$prep->bindParam(':fileID', $fileID);
		$prep->execute();

		return $prep->fetchColumn() > 0;
	}

	public function generateFileID($hash, $length = 8) {
		while(!isset($id) || $this->fileIDExists($id)) {
			$id = generateAnID($hash, $length);
		}

		return $id;
	}

	/**
	 * Returns a new ID for the file based on the hash file
	 *
	 * BSD license http://stackoverflow.com/a/1516430/2650468
	 *
	 * @param int $length is the length of the ID returned, 8 by default
	 * @return string The generated file ID
	 */
	private function generateAnID($hash, $length = 8) {
		$hex = md5($hash.'?*'.uniqid("", true));

		$pack = pack('H*', $hex);

		// max 22 chars
		$uid = base64_encode($pack);

		// mixed case
		$uid = ereg_replace("[^A-Za-z0-9]", "", $uid);

		if ($len < 4) {
			$len = 4;
		}

		if ($len > 128) {
			// prevent silliness, can remove
			$len = 128;
		}

		while (strlen($uid) < $len) {
			// append until length achieved
			$uid = $uid.$this->generateFileID($hash, 22);
		}

		return substr($uid, 0, $len);
	}
}
