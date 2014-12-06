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
	/**
	 * Configuration handler
	 * 
	 * @return array Settings model
	 */
	protected function configure() {
		// Settings editable by user
		$settings_keys = array('site_title', 'description', 'email');
		
		// Get 
		$settings = array();
		$system_settings = WConfig::get('config');
		foreach ($settings_keys as $key) {
			$settings[$key] = isset($system_settings[$key]) ? $system_settings[$key] : '';
		}
		
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
			
			WConfig::save('config');
			WNote::success('settings_updated', WLang::_('settings_updated'));
		}
		
		// Return settings values
		return $settings;
	}
}

?>
