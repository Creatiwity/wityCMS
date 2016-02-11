<?php
/**
 * Settings Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SettingsAdminController is the Admin Controller of the Settings Application
 *
 * @package Apps\Settings\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class SettingsAdminController extends WController {
	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'settings'.DS;
	}

	/**
	 * Configuration handler
	 *
	 * @return array Settings model
	 */
	protected function configure(array $params) {
		// Settings editable by user
		$settings_keys = array('name', 'page_title', 'page_description', 'email');

		$settings = array();
		foreach ($settings_keys as $key) {
			$settings[$key] = WConfig::get('config.'.$key, '');
		}

		$settings['favicon'] = WConfig::get('config.favicon', '');
		$settings['icon'] = WConfig::get('config.icon', '');

		// Update settings
		if (WRequest::getMethod() == 'POST') {
			$data = WRequest::getAssoc(array('update', 'settings'));

			foreach ($settings_keys as $key) {
				if (isset($data['settings'][$key])) {
					// Direct user input: all characters are accepted here
					$settings[$key] = $data['settings'][$key];
					WConfig::set('config.'.$key, $settings[$key]);
				}
			}

			// Uploads favicon & icon
			foreach (array('favicon', 'icon') as $file) {
				if (!empty($_FILES[$file]['name'])) {
					$this->makeUploadDir();

					$upload = WHelper::load('upload', array($_FILES[$file]));
					$upload->allowed = array('image/*');
					$upload->file_new_name_body = $file;
					$upload->file_overwrite = true;

					$upload->Process($this->upload_dir);

					if (!$upload->processed) {
						WNote::error($file.'_upload_error', $upload->error);
					} else {
						$old_file = WConfig::get('config.'.$file);

						WConfig::set('config.'.$file, '/upload/settings/'.$upload->file_dst_name.'?'.time());
					}
				}
			}

			WConfig::save('config');

			WNote::success('settings_updated', WLang::get('The settings were updated successfully.'));
			$this->setHeader('Location', WRoute::getDir().'admin/settings/');
		}

		// Return settings values
		return $settings;
	}

	/**
	 * Languages handler
	 *
	 * @return array languages
	 */
	protected function languages(array $params) {
		$action = array_shift($params);

		if ($action == 'language_add') {
			return $this->language_form();
		} else {
			return Array(
				'form'      => false,
				'languages' => $this->model->getLanguages());
		}
	}

	private function language_form($id_language = 0, $db_data = array()) {
		if (WRequest::getMethod() == 'POST') {
			$post_data = WRequest::getAssoc(array('name', 'iso', 'code', 'date_format_short', 'date_format_long', 'enabled', 'is_default'), null, 'POST');
			$errors = array();

			/* BEGING VARIABLES CHECKING */
			$required = array('name', 'iso', 'code');
			foreach ($required as $req) {
				if (empty($post_data[$req])) {
					$errors[] = WLang::get('Please, provide a '.$req.'.');
				}
			}
			/* END VARIABLES CHECKING */

			$post_data['iso'] = strtolower($post_data['iso']);
			$post_data['enabled'] = $post_data['enabled'] == 'on';

			$languages = WLang::getLangIds();
			if (empty($languages)) {
				$post_data['is_default'] = true;
			} else {
				$post_data['is_default'] = $post_data['is_default'] == 'on';
			}

			if (empty($errors)) {
				if (empty($id_language)) { // Add case
					if ($id_language = $this->model->insertLanguage($post_data)) {
						$db_data = $this->model->getLanguage($id_language);
						WNote::success('language_added', WLang::get('The language was successfully created.'));
					} else {
						$db_data = $post_data;
						WNote::error('language_not_added', WLang::get('An error occured during the creation of the language.'));
					}
				} else { // Edit case
					if ($this->model->updateLanguage($id_language, $post_data)) {
						$db_data = $this->model->getLanguage($id_language);
						WNote::success('language_edited', WLang::get('The language was successfully edited.'));
					} else {
						$db_data = $post_data;
						WNote::error('language_not_edited', WLang::get('An error occured during the edition of the language.'));
					}
				}

				if ($post_data['is_default']) {
					$this->model->setDefaultLanguage($db_data['id']);
				}

				$this->setHeader('Location', WRoute::getDir().'admin/settings/languages');
			} else {
				WNote::error('language_data_error', implode('<br />', $errors));

				if (empty($id_language)) {
					$this->setHeader('Location', WRoute::getDir().'admin/settings/language_add');
				} else {
					$post_data['id'] = $id_language;
					$this->setHeader('Location', WRoute::getDir().'admin/settings/language_edit/'.$id_language);
				}

				// Restore fields
				$_SESSION['settings_languages_post_data'] = $post_data;
			}
		} else if (!empty($_SESSION['settings_languages_post_data'])) {
			$db_data = $_SESSION['settings_languages_post_data'];
			unset($_SESSION['settings_languages_post_data']);
		}

		return $db_data;
	}

	protected function language_add(array $params) {
		return $this->language_form();
	}

	protected function language_edit(array $params) {
		$id_language = intval(array_shift($params));

		$db_data = $this->model->getLanguage($id_language);

		if (!empty($db_data)) {
			return $this->language_form($id_language, $db_data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/language');
			WNote::error('language_not_found');
			return array();
		}
	}

	protected function language_delete(array $params) {
		$id_language = intval(array_shift($params));

		$language = $this->model->getLanguage($id_language);

		if (!empty($language)) {
			if (in_array('confirm', $params)) {

				$this->model->deleteLanguage($id_language);

				$this->setHeader('Location', WRoute::getDir().'admin/settings/languages');
				WNote::success('The language was successfully deleted.');
			}

			return $language;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/settings/languages');
			WNote::error('language_not_found');
		}

		return array();
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
