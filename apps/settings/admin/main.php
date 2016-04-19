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

	private $EXCLUDED_THEMES = array('system', 'admin-bootstrap');
	private $EXCLUDED_APPS   = array('admin');
	private $EXCLUDED_DIRS   = array('.', '..');

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'settings'.DS;
	}

	/**
	 * General handler
	 */
	protected function general(array $params) {
		$settings_keys = array(
			// General
			'site_title', 'base', 'theme', 'timezone', 'favicon', 'ga', 'version', 'debug',
			// SEO
			'page_title', 'page_description',
			// OpenGraph
			'og_title', 'og_description', 'og_image',
			// Coordinates
			'coord_address', 'coord_zip', 'coord_city', 'coord_state', 'coord_country', 'email', 'coord_phone'
		);
		$route_keys = array('default_front', 'default_admin');

		$settings = array();
		foreach ($settings_keys as $key) {
			$settings[$key] = WConfig::get('config.'.$key, '');
		}

		$route = array();
		foreach ($route_keys as $key) {
			$route[$key] = WConfig::get('route.'.$key, '');
		}

		// Update settings
		if (WRequest::getMethod() == 'POST') {
			$data = WRequest::getAssoc(array('update', 'settings', 'route'));

			foreach ($settings_keys as $key) {
				if (isset($data['settings'][$key])) {
					if ($key == 'debug') {
						$settings['debug'] = ($data['settings']['debug'] == 'on');
						WConfig::set('config.debug', $settings['debug']);
					} else if ($key == 'ga') {
						if (!empty($data['settings']['ga']) && !preg_match('/^ua-\d{4,9}-\d{1,4}$/i', $data['settings']['ga'])) {
							WNote::error('settings_error', WLang::get('The Google Analytics tracking code is not correct.'));
						} else {
							$settings['ga'] = $data['settings']['ga'];
							WConfig::set('config.ga', $settings['ga']);
						}
					} else if ($key == 'email') {
						if (!empty($data['settings']['email']) && !WTools::isEmail($data['settings']['email'])) {
							WNote::error('settings_error', WLang::get('The email provided is not valid.'));
						} else {
							$settings['email'] = $data['settings']['email'];
							WConfig::set('config.email', $settings['email']);
						}
					} else {
						// Direct user input: all characters are accepted here
						$settings[$key] = $data['settings'][$key];
						WConfig::set('config.'.$key, $settings[$key]);
					}
				}
			}

			foreach ($route_keys as $key) {
				if (isset($data['route'][$key])) {
					$route[$key] = $data['route'][$key];
					WConfig::set('route.'.$key, $route[$key]);
				}
			}

			// Uploads favicon & image
			foreach (array('favicon', 'og_image') as $file) {
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

						if (!empty($old_file) && basename($old_file) != $upload->file_dst_name) {
							@unlink(WITY_PATH.$old_file);
						}

						WConfig::set('config.'.$file, '/upload/settings/'.$upload->file_dst_name.'?'.time());
					}
				}
			}

			WConfig::save('config');
			WConfig::save('route');

			if (!empty($params['suffix'])) {
				$suffix = $params['suffix'];
			} else {
				$suffix = 'general';
			}

			$this->setHeader('Location', WRoute::getDir().'admin/settings/'.$suffix);
			return WNote::success('settings_updated', WLang::get('The settings were updated successfully.'));
		}

		// Return settings values
		return array(
			'settings'    => $settings,
			'route'       => $route,
			'front_apps'  => $this->getAllFrontApps(),
			'admin_apps'  => $this->getAllAdminApps(),
			'themes'      => $this->getAllThemes(),
			'countries'   => WTools::getCountries(),
		);
	}

	/**
	 * SEO handler
	 */
	protected function seo($params) {
		return $this->general(array('suffix' => 'seo'));
	}

	/**
	 * Coordinates handler
	 */
	protected function coordinates($params) {
		return $this->general(array('suffix' => 'coordinates'));
	}

	/**
	 * Languages handler
	 */
	protected function languages(array $params) {
		return array(
			'languages' => $this->model->getLanguages()
		);
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

	/**
	 * Get existing themes
	 *
	 * @return array List of themes
	 */
	private function getAllThemes() {
		if ($themes = scandir(THEMES_DIR)) {
			foreach ($themes as $key => $value) {
				if (in_array($value, $this->EXCLUDED_THEMES) || !is_dir(THEMES_DIR.DS.$value) || in_array($value, $this->EXCLUDED_DIRS)) {
					unset($themes[$key]);
				}
			}

			$themes[] = "_blank";
		}

		return $themes;
	}

	/**
	 * Get existing Front Apps
	 *
	 * @return array List of Front Apps
	 */
	private function getAllFrontApps() {
		if ($scanned_apps = scandir(APPS_DIR)) {
			foreach ($scanned_apps as $key => $value) {
				if (!in_array($value, $this->EXCLUDED_APPS) && is_dir(APPS_DIR.DS.$value.DS."front") && !in_array($value, $this->EXCLUDED_DIRS)) {
					$apps[$key] = $value;
				}
			}
		}

		return $apps;
	}

	/**
	 * Get existing Admin Apps
	 *
	 * @return array List of Admin Apps
	 */
	private function getAllAdminApps() {
		if ($scanned_apps = scandir(APPS_DIR)) {
			foreach ($scanned_apps as $key => $value) {
				if (!in_array($value, $this->EXCLUDED_APPS) && is_dir(APPS_DIR.DS.$value.DS."admin") && !in_array($value, $this->EXCLUDED_DIRS)) {
					$apps[$key] = 'admin/'.$value;
				}
			}
		}

		return $apps;
	}
}

?>
