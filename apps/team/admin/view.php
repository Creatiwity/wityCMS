<?php
/**
 * Team Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * TeamAdminView is the Admin View of the Team Application.
 *
 * @package Apps\Team\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class TeamAdminView extends WView {
	public function members(array $model) {
		$this->assign('require', 'witycms/admin');
		$this->assign('members', $model['members']);
	}

	private function memberForm(array $model) {
		$this->assign('js', '/libraries/ckeditor/ckeditor.js');
		$this->assign('require', 'witycms/admin');

		$default = array(
			'id'          => '',
			'name'        => '',
			'email'       => '',
			'linkedin'    => '',
			'twitter'     => '',
			'image'       => '',
		);
		$default_translatable = array(
			'title'       => '',
			'description' => '',
		);

		$lang_list = WLang::getLangIds();
		foreach ($default_translatable as $key => $value) {
			foreach ($lang_list as $id_lang) {
				$default[$key.'_'.$id_lang] = $value;
			}
		}

		$this->assignDefault($default, $model['data']);

		// Auto-translate
		$form_values = array();
		foreach ($default as $item => $def) {
			$form_values[$item] = isset($model['data'][$item]) ? $model['data'][$item] : $def;
		}
		$this->assign('form_values', json_encode($form_values));

		$this->setTemplate('member-form.html');
	}

	public function memberAdd(array $model) {
		$this->memberForm($model);
	}

	public function memberEdit(array $model) {
		$this->memberForm($model);
	}

	public function memberDelete(array $model) {
		$this->assign('name', $model['name']);
		$this->assign('confirm_delete_url', '/admin/team/member-delete/'.$model['id'].'/confirm');
	}
}

?>
