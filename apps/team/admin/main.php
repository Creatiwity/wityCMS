<?php
/**
 * Team Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * TeamAdminController is the Admin Controller of the Team Application.
 *
 * @package Apps\Team\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class TeamAdminController extends WController {
	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'team'.DS;
	}

	protected function members(array $params) {
		return array(
			'members' => $this->model->getMembers()
		);
	}

	private function memberForm($id_member = 0, $db_data = array()) {
		if (WRequest::getMethod() == 'POST') {
			$errors = array();
			$data = WRequest::getAssoc(array('name', 'email', 'linkedin', 'twitter'), null, 'POST');
			$data_translatable = array();

			// Format translatable fields
			$translatable_fields = array('title', 'description');
			$lang_list = WLang::getLangIds();
			$default_id = WLang::getDefaultLangId();

			foreach ($translatable_fields as $field) {
				foreach ($lang_list as $i => $id_lang) {
					$value = WRequest::get($field.'_'.$id_lang);

					if (empty($value) && $id_lang != $default_id) {
						// Use the value of the default lang
						$data_translatable[$id_lang][$field] = $data_translatable[$default_id][$field];
					} else {
						$data_translatable[$id_lang][$field] = $value;
					}
				}
			}

			/* BEGING VARIABLES CHECKING */
			if (empty($data['name'])) {
				$errors[] = WLang::get('Please, provide a name.');
			}

			if (empty($data_translatable[$default_id]['title'])) {
				$errors[] = WLang::get('Please, provide a position.');
			}
			/* END VARIABLES CHECKING */

			// Image
			if (!empty($_FILES['image']['name'])) {
				$this->makeUploadDir();

				$upload = WHelper::load('upload', array($_FILES['image']));
				$upload->allowed = array('image/*');

				$upload->Process($this->upload_dir);

				if (!$upload->processed) {
					$errors[] = $upload->error;
					$data['image'] = $db_data['image'];
				} else {
					$data['image'] = '/upload/team/'.$upload->file_dst_name;

					// Erase the previous image
					if (!empty($db_data['image'])) {
						@unlink($this->upload_dir.basename($db_data['image']));
					}
				}
			} else if (!empty($id_member)) {
				$data['image'] = $db_data['image'];
			} else {
				$data['image'] = '';
			}

			// Upload
			if (!empty($_FILES['image_hover']['name'])) {
				$this->makeUploadDir();

				$upload = WHelper::load('upload', array($_FILES['image_hover']));
				$upload->allowed = array('image/*');

				$upload->Process($this->upload_dir);

				if (!$upload->processed) {
					$errors[] = $upload->error;
					$data['image_hover'] = $db_data['image_hover'];
				} else {
					$data['image_hover'] = '/upload/team/'.$upload->file_dst_name;

					// Erase the previous image_hover
					if (!empty($db_data['image_hover'])) {
						@unlink($this->upload_dir.basename($db_data['image_hover']));
					}
				}
			} else if (!empty($id_member)) {
				$data['image_hover'] = $db_data['image_hover'];
			} else {
				$data['image_hover'] = '';
			}

			if (empty($errors)) {
				if (empty($id_member)) { // Add case
					if ($id_member = $this->model->createMember($data, $data_translatable)) {
						WNote::success('member_added', WLang::get('The member was successfully created.', $data['name']));
					} else {
						WNote::error('member_not_added', WLang::get('An unknown error occured.'));
					}

					$this->setHeader('Location', WRoute::getDir().'admin/team/member-edit/'.$id_member);
				} else { // Edit case
					if ($this->model->updateMember($id_member, $data, $data_translatable)) {
						WNote::success('member_edited', WLang::get('The member was successfully edited.', $data['name']));
					} else {
						WNote::error('member_not_edited', WLang::get('An unknown error occured.'));
					}

					$this->setHeader('Location', WRoute::getDir().'admin/team/member-edit/'.$id_member);
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
				$db_data = $data;
			}
		}

		return array(
			'id'    => $id_member,
			'data'  => $db_data
		);
	}

	protected function memberAdd(array $params) {
		return $this->memberForm();
	}

	protected function memberEdit(array $params) {
		$id_member = intval(array_shift($params));

		$data = $this->model->getMember($id_member);

		// Check whether this item exists
		if (!empty($data)) {
			return $this->memberForm($id_member, $data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/team');
			return WNote::error('member_not_found', WLang::get('The member was not found.'));
		}
	}

	protected function memberDelete(array $params) {
		$id_member = intval(array_shift($params));
		$data = $this->model->getMember($id_member);

		// Check existence
		if ($data === false) {
			$this->setHeader('Location', WRoute::getDir().'admin/team');
			return WNote::error('member_not_found', WLang::get('The member was not found.'));
		}

		if (in_array('confirm', $params)) {
			$this->model->deleteMember($id_member);

			$this->setHeader('Location', WRoute::getDir() . 'admin/team');
			WNote::success('member_deleted', WLang::get('The member was successfully deleted.'));
		}

		return $data;
	}

	/**
	 * Reorders elements
	 *
	 * @return array WNote
	 */
	protected function membersReorder(array $params) {
		if (WRequest::hasDataForURL('admin/team/members-reorder')) {
			$positions = WRequest::get('positions', null, 'POST');

			foreach ($positions as $id => $position) {
				$id = intval($id);

				if (!empty($id)) {
					$this->model->reorderElement($id, intval($position));
				}
			}

			return WNote::success('reordering_success');
		} else {
			return WNote::error('data_missing');
		}
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
