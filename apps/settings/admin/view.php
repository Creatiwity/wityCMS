<?php
/**
 * Settings Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SettingsAdminView is the Admin View of the Settings Application
 * 
 * @package Apps\Settings\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-22-10-2014
 */
class SettingsAdminView extends WView {
	/**
	 * Prepares the configure view
	 */
	public function configure($model) {
		$this->assign('settings', $model);
	}
}

?>
