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
 * @version 0.5.0-dev-22-10-2014
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
	protected function configure() {
		// Settings editable by user
		$settings_keys = array('site_title', 'description', 'email');

		$settings = array();
		foreach ($settings_keys as $key) {
			$settings[$key] = WConfig::get('config.'.$key, '');
		}

		$settings['favicon'] = WConfig::get('config.favicon', '');
		$settings['icon'] = WConfig::get('config.icon', '');
		
		// Update settings
		$data = WRequest::getAssoc(array('update', 'settings'));
		if ($data['update'] == 'true') {
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

						// Erase the previous image
						if (!empty($old_file)) {
							$old_file_split = explode('?', $old_file);
							$old_file = array_shift($old_file_split);
							@unlink($this->upload_dir.basename($old_file));
						}
						
						WConfig::set('config.'.$file, '/upload/settings/'.$upload->file_dst_name.'?'.time());
					}
				}
			}
			
			WConfig::save('config');

			WNote::success('settings_updated', WLang::get('settings_updated'));
			$this->setHeader('Location', WRoute::getDir().'admin/settings/');
		}
		
		// Return settings values
		return $settings;
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
