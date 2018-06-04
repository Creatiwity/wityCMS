<?php
/**
 * Settings Application - Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SettingsModel is the Model of the Settings Application
 *
 * @package Apps\Settings\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class SettingsAdminModel {
	/**
	 * @var WDatabase instance
	 */
	protected $db;

	public function __construct() {
		$this->db = WSystem::getDB();

		// Declare table
		$this->db->declareTable('languages');
	}

	/**
	 * Retrieve languages from database
	 *
	 * @return Array
	 */
	public function getLanguages() {
		$prep = $this->db->query('SELECT * FROM languages');

		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieve a specific language from database
	 *
	 * @param int $id
	 * @return Array
	 */
	public function getLanguage($id) {
		$prep = $this->db->prepare('SELECT * FROM languages WHERE id = ?');
		$prep->execute(array($id));

		return $prep->fetch();
	}

	/**
	 * Create a language
	 *
	 * @param Array $data
	 * @return Bool success
	 */
	public function insertLanguage($data) {
		if ($data['is_default']) {
			$data['enabled'] = true;
		}

		return $this->db->insertInto('languages',
			array('name', 'iso', 'code', 'date_format_short', 'date_format_long', 'enabled'),
			$data
		);
	}

	/**
	 * Update a language
	 *
	 * @param Array $data
	 * @return Bool success
	 */
	public function updateLanguage($id, $data) {
		if ($id == WLang::getDefaultLangId() || $data['is_default']) {
			$data['enabled'] = true;
		}

		return $this->db->update('languages',
			array('name', 'iso', 'code', 'date_format_short', 'date_format_long', 'enabled'),
			$data,
			'id = '.$id
		);
	}

	/**
	 * Delete a language
	 *
	 * @param Int $id
	 * @return Bool success
	 */
	public function deleteLanguage($id) {
		$prep = $this->db->prepare('
			DELETE FROM languages WHERE id = :id
		');
		$prep->bindParam(':id', $id, PDO::PARAM_INT);

		return $prep->execute();
	}

	/**
	 * Change default language
	 *
	 * @param int Id
	 * @return Bool success
	 */
	public function setDefaultLanguage($id) {
		$this->db->update('languages',
			array('is_default'),
			array('is_default' => 0)
		);

		$this->db->update('languages',
			array('is_default', 'enabled'),
			array('is_default' => 1,'enabled' => 1),
			'id = '.$id
		);

		return true;
	}

	public function getTranslatablesInFolder($folder, array $languages) {
		$translatables = array();

		foreach ($languages as $language) {
			$file = $folder.strtolower($language['iso']).'.xml';

			if (file_exists($file)) {
				$translatables[$language['id']] = $this->getTranslatablesOfFile($file);
			}
		}

		return $translatables;
	}

	public function getTranslatablesOfFile($file) {
		$translatables = array();

		// Checks that file exists and not already loaded
		if (file_exists($file)) {
			// Parses XML file
			$string = file_get_contents($file);
			$xml = new SimpleXMLElement($string);
			foreach ($xml->item as $lang_item) {
				$lang_key = (string) $lang_item->attributes()->id;
				$lang_string = dom_import_simplexml($lang_item)->nodeValue;

				$translatables[md5($lang_key)] = $lang_string;
			}
		}

		return $translatables;
	}
}

?>
