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
 * @version 0.5.0-11-02-2016
 */
class SettingsAdminView extends WView {
	/**
	 * Prepares the general view
	 */
	public function general($model) {
		$this->assign('settings', $model['settings']);
		$this->assign('route', $model['route']);
		$this->assign('front_apps', $model['front_apps']);
		$this->assign('admin_apps', $model['admin_apps']);
		$this->assign('themes', $model['themes']);
	}

	/**
	 * Prepares the SEO view
	 */
	public function seo($model) {
		$this->assign('settings', $model['settings']);
	}

	/**
	 * Prepares the Coordinates view
	 */
	public function coordinates($model) {
		$this->assign('settings', $model['settings']);
		$this->assign('countries', $model['countries']);
	}

	public function languages($data) {
		$this->assign($data);
	}

	private function language_form($model) {
		$this->assign('css', '/apps/settings/admin/css/settings.css');
		$this->assign('require', 'witycms/admin');
		$default = array(
			'id'                => 0,
			'name'              => '',
			'iso'               => '',
			'code'              => '',
			'date_format_short' => '%d/%m/%Y',
			'date_format_long'  => '%d/%m/%Y %H:%M',
			'enabled'           => 1,
			'is_default'        => 0
		);
		$this->assignDefault($default, $model);
		$this->setTemplate('language_form.html');
	}

	public function language_add($model) {
		$this->language_form($model);
	}

	public function language_edit($model) {
		$this->language_form($model);
	}

	public function language_delete($model) {
		$this->assign('name', $model['name']);
		$this->assign('confirm_delete_url', '/admin/settings/language_delete/'.$model['id'].'/confirm');
	}
}

?>
