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
 * @version 0.6.2-04-06-2018
 */
class SettingsAdminController extends WController {
	private $upload_dir;

	private $EXCLUDED_THEMES = array('system', 'admin-bootstrap');
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
			'site_title', 'base', 'theme', 'timezone', 'favicon', 'ga', 'version', 'debug', 'anti_flood',
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
				if ($key == 'debug' || $key == 'anti_flood') {
					$settings[$key] = (isset($data['settings'][$key]) && $data['settings'][$key] == 'on');
					WConfig::set('config.'.$key, $settings[$key]);
				} else if (isset($data['settings'][$key])) {
					if ($key == 'ga') {
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
			'front_apps'  => $this->getApps(false),
			'admin_apps'  => $this->getApps(true),
			'themes'      => $this->getAllThemes($this->EXCLUDED_THEMES),
			'countries'   => WLang::getCountries(),
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
					$errors[] = WLang::get('Please, fill in the field %s.', $req);
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

	protected function translate() {
		return array(
			'apps'   => $this->getApps(),
			'themes' => $this->getAllThemes(array('system', '_blank'))
		);
	}

	protected function translate_app(array $params) {
		if (!isset($params[0])) {
			return WNote::error('no_app_provided', WLang::_('The name of the app to translate is missing.'));
		}

		$app = $params[0];
		$manifest = $this->loadManifest($app);
		$folder = APPS_DIR.$app;

		if (empty($manifest)) {
			return WNote::error('app_does_not_exist', WLang::_('The app to translate does not exist.'));
		}

		$model = $this->translate_files('app', $manifest['name'], $folder);

		// Add App name
		$new_hashes = array(
			md5($manifest['name']) => $manifest['name']
		);

		// Add Front actions
		if (!empty($manifest['actions'])) {
			foreach ($manifest['actions'] as $action) {
				$hash = md5($action['description']);
				$new_hashes[$hash] = $action['description'];
			}
		}

		// Add Admin actions
		if (!empty($manifest['admin'])) {
			foreach ($manifest['admin'] as $action) {
				$hash = md5($action['description']);
				$new_hashes[$hash] = $action['description'];
			}
		}

		foreach ($new_hashes as $hash => $value) {
			$model['hashes']['admin'][$hash] = $value;

			foreach (WLang::getLangs() as $language) {
				if (!empty($model['translatables_file']['admin'][$language['id']][$hash])) {
					$model['translatables']['admin_fields_'.$language['id'].'['.$hash.']'] = $model['translatables_file']['admin'][$language['id']][$hash];
				}
			}
		}

		$model['fields'][] = array(
			'prefix'        => 'admin',
			'file'          => 'General',
			'hash'          => $new_hashes,
			'keys'          => array_values($new_hashes),
			'translatables' => array()
		);

		if (WRequest::getMethod() == 'POST') {
			$data = WRequest::getAssoc(array('type', 'folder'));
			$data_translatable = array();

			// Format translatable fields
			$translatable_fields = array('front_fields', 'admin_fields');
			$langs = WLang::getLangs();

			foreach ($translatable_fields as $field) {
				foreach ($langs as $lang) {
					$data_translatable[$lang['id']][$field] = WRequest::get($field.'_'.$lang['id']);
				}
			}

			// Write files
			foreach ($langs as $lang) {
				if (!empty($model['hashes']['front'])) {
					$this->saveAsXMLForLang(
						$lang,
						$model['hashes']['front'],
						$data_translatable[$lang['id']]['front_fields'],
						$folder.DS.'front'.DS.'lang'.DS.strtolower($lang['iso']).'.xml'
					);
				}

				if (!empty($model['hashes']['admin'])) {
					$this->saveAsXMLForLang(
						$lang,
						$model['hashes']['admin'],
						$data_translatable[$lang['id']]['admin_fields'],
						$folder.DS.'admin'.DS.'lang'.DS.strtolower($lang['iso']).'.xml'
					);
				}
			}

			$this->setHeader('Location', WRoute::getDir().'admin/settings/translate');
			return WNote::success('translation_updated', WLang::_('The translations were successfully updated.'));
		} else {
			return $model;
		}
	}

	private function saveAsXMLForLang($lang, $hashes, $fields, $file) {
		if (empty($fields)) {
			return;
		}

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><lang value="'.$lang['iso'].'"></lang>');

		foreach ($fields as $hash => $value) {
			if (!empty($hashes[$hash]) && !empty($value)) {
				$item = $xml->addChild('item', htmlspecialchars($value));
				$item->addAttribute('id', $hashes[$hash]);
			}
		}

		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		file_put_contents($file, $dom->saveXML());
	}

	protected function translate_theme(array $params) {
		if (!isset($params[0])) {
			return WNote::error('no_theme_provided', WLang::_('The name of the theme to translate is missing.'));
		}

		$theme = $params[0];

		if (!is_dir($folder = THEMES_DIR.$theme)) {
			return WNote::error('theme_does_not_exist', WLang::_('The theme to translate does not exist.'));
		}

		$model = $this->translate_files('theme', $theme, $folder);

		if (WRequest::getMethod() == 'POST') {
			$data = WRequest::getAssoc(array('type', 'folder'));
			$data_translatable = array();

			// Format translatable fields
			$translatable_fields = array('theme_fields');
			$langs = WLang::getLangs();

			foreach ($translatable_fields as $field) {
				foreach ($langs as $lang) {
					$data_translatable[$lang['id']][$field] = WRequest::get($field.'_'.$lang['id']);
				}
			}

			// Write files
			foreach ($langs as $lang) {
				if (!empty($model['hashes']['theme'])) {
					$this->saveAsXMLForLang(
						$lang,
						$model['hashes']['theme'],
						$data_translatable[$lang['id']]['theme_fields'],
						$folder.DS.'lang'.DS.strtolower($lang['iso']).'.xml'
					);
				}
			}

			$this->setHeader('Location', WRoute::getDir().'admin/settings/translate');
			return WNote::success('translation_updated', WLang::_('The translations were successfully updated.'));
		} else {
			return $model;
		}
	}

	private function translate_files($type = 'core', $name, $folder = SYSTEM_DIR) {
		$languages = WLang::getLangs();
		$fields = array();
		$translatables_file = array();
		$translatables = array();
		$hashes = array();

		switch ($type) {
			case 'app':
				$foldersToScan = array(
					$folder.DS.'front',
					$folder.DS.'front'.DS.'templates',
					$folder.DS.'admin',
					$folder.DS.'admin'.DS.'templates'
				);

				$extensionsToScan = array('html', 'php');

				$translatables_file['front'] = $this->model->getTranslatablesInFolder($folder.DS.'front'.DS.'lang'.DS, $languages);
				$translatables_file['admin'] = $this->model->getTranslatablesInFolder($folder.DS.'admin'.DS.'lang'.DS, $languages);
				break;

			case 'theme':
				$foldersToScan = array(
					$folder.DS.'templates'
				);

				$extensionsToScan = array('html');

				$translatables_file['theme'] = $this->model->getTranslatablesInFolder($folder.DS.'lang'.DS, $languages);
				break;

			default:
				break;
		}

		foreach ($foldersToScan as $folderToScan) {
			if (!is_dir($folderToScan) || !($scan = scandir($folderToScan))) {
				continue;
			}

			foreach ($scan as $file) {
				if (!is_file($folderToScan.DS.$file)) {
					continue;
				}

				$fileExtension = pathinfo($file, PATHINFO_EXTENSION);

				if (!in_array($fileExtension, $extensionsToScan)) {
					continue;
				}

				$fileTranslatables = array(
					'file'          => str_replace($folder.DS, '', $folderToScan.DS.$file),
					'keys'          => array(),
					'translatables' => array()
				);

				if ($type == 'theme') {
					$fileTranslatables['prefix'] = 'theme';
				} else {
					$fileTranslatables['prefix'] = strpos($folderToScan, 'admin') !== false ? 'admin' : 'front';
				}

				// Scan file content
				$file_content = file_get_contents($folderToScan.DS.$file);

				if (preg_match_all('#\{lang ([^\|$]+)(\|.+)?\}#U', $file_content, $matches)) {
					foreach ($matches[1] as $value) {
						if (!in_array($value, $fileTranslatables['keys'])) {
							array_push($fileTranslatables['keys'], $value);
						}
					}
				}

				if (preg_match_all("#WLang::(get|_)\('([^\']+)'#U", $file_content, $matches)) {
					foreach ($matches[2] as $value) {
						if (!in_array($value, $fileTranslatables['keys'])) {
							array_push($fileTranslatables['keys'], $value);
						}
					}
				}

				if (preg_match_all("#WLang::(get|_)\(\"([^\"]+)\"#U", $file_content, $matches)) {
					foreach ($matches[2] as $value) {
						if (!in_array($value, $fileTranslatables['keys'])) {
							array_push($fileTranslatables['keys'], $value);
						}
					}
				}

				if (empty($fileTranslatables['keys'])) {
					continue;
				}

				foreach ($fileTranslatables['keys'] as $key) {
					$key_hash = md5($key);

					if (isset($hashes[$fileTranslatables['prefix']][$key_hash])) {
						continue;
					}

					$fileTranslatables['hash'][$key_hash] = $key;
					$hashes[$fileTranslatables['prefix']][$key_hash] = $key;

					foreach ($languages as $language) {
						if (!empty($translatables_file[$fileTranslatables['prefix']][$language['id']][$key_hash])) {
							$translatables[$fileTranslatables['prefix'].'_fields_'.$language['id'].'['.$key_hash.']'] = $translatables_file[$fileTranslatables['prefix']][$language['id']][$key_hash];
						}
					}
				}

				array_push($fields, $fileTranslatables);
			}
		}

		return array(
			'type'               => $type,
			'folder'             => $folder,
			'languages'          => $languages,
			'translatables'      => $translatables,
			'fields'             => $fields,
			'translatables_file' => $translatables_file,
			'hashes'             => $hashes
		);
	}

	/**
	 * Get existing themes
	 *
	 * @return array List of themes
	 */
	private function getAllThemes($excluded_themes = array()) {
		if ($themes = scandir(THEMES_DIR)) {
			foreach ($themes as $key => $value) {
				if (in_array($value, $excluded_themes) || !is_dir(THEMES_DIR.DS.$value) || in_array($value, $this->EXCLUDED_DIRS)) {
					unset($themes[$key]);
				}
			}

			if (!in_array('_blank', $excluded_themes)) {
				$themes[] = '_blank';
			}
		}

		return $themes;
	}
}

?>
