<?php
/**
 * Slideshow Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SlideshowAdminController is the Admin Controller of the Slideshow Application
 *
 * @package Apps\Slideshow\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class SlideshowAdminController extends WController {
	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'slideshow'.DS;
	}

	protected function slides(array $params) {
		return $this->model->getSlides();
	}

	private function slide_form($id_slide = 0, $db_data = array()) {
		if (!empty($_POST)) {
			$errors = array();
			$post_data = WRequest::getAssoc(array('url'), null, 'POST');
			$data_translatable = array();

			// Format translatable fields
			$translatable_fields = array('title', 'legend');
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
			if (empty($data_translatable[$default_id]['title'])) {
				$errors[] = WLang::get('Please, provide a title.');
			}

			$post_data['url'] = WTools::secureURL($post_data['url']);
			/* END VARIABLES CHECKING */

			// Image
			if (!empty($_FILES['image']['name'])) {
				$this->makeUploadDir();

				$upload = WHelper::load('upload', array($_FILES['image']));
				$upload->allowed = array('image/*');

				$upload->Process($this->upload_dir);

				if (!$upload->processed) {
					$errors[] = $upload->error;
				} else {
					$post_data['image'] = '/upload/slideshow/'.$upload->file_dst_name;

					// Erase the previous image
					if (!empty($db_data['image']) && $post_data['image'] != $db_data['image']) {
						@unlink($this->upload_dir.basename($db_data['image']));
					}
				}
			} else if (!empty($id_slide)) {
				$post_data['image'] = $db_data['image'];
			} else {
				$errors[] = WLang::get('Please, provide an image.');
			}

			if (empty($errors)) {
				if (empty($id_slide)) { // Add case
					$id_slide = $this->model->insertSlide($post_data, $data_translatable);

					if ($id_slide) {
						$this->setHeader('Location', WRoute::getDir().'admin/slideshow/edit/'.$id_slide);
						WNote::success('slide_added', WLang::get('The slide was successfully created.'));

						$db_data = $this->model->getSlide($id_slide);
					} else {
						$db_data = $post_data;
						WNote::error('slide_not_added', WLang::get('An unknown error occured.'));
					}
				} else { // Edit case
					if ($this->model->updateSlide($id_slide, $post_data, $data_translatable)) {
						$this->setHeader('Location', WRoute::getDir().'admin/slideshow/edit/'.$id_slide);
						WNote::success('slide_edited', WLang::get('The slide was successfully edited.'));
					} else {
						$db_data = $post_data;
						WNote::error('slide_not_edited', WLang::get('An unknown error occured.'));
					}
				}
			} else {
				WNote::error('slide_data_error', implode('\n<br />', $errors));

				if (!empty($post_data['image'])) {
					@unlink($this->upload_dir.basename($post_data['image']));
					$post_data['image'] = '';
				}

				// Restore fields
				$db_data = $post_data;

				foreach ($data_translatable as $id_lang => $values) {
					foreach ($values as $key => $value) {
						$db_data[$key.'_'.$id_lang] = $value;
					}
				}
			}
		}

		return $db_data;
	}

	protected function slide_add(array $params) {
		return $this->slide_form();
	}

	protected function slide_edit(array $params) {
		$id_slide = intval(array_shift($params));

		$db_data = $this->model->getSlide($id_slide);

		if (!empty($db_data)) {
			return $this->slide_form($id_slide, $db_data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/slideshow');
			WNote::error('slide_not_found', WLang::get('The slide was not found.'));
			return array();
		}
	}

	public function slide_delete(array $params) {
		$id_slide = intval(array_shift($params));

		$slide = $this->model->getSlide($id_slide);

		if (!empty($slide)) {
			if (in_array('confirm', $params)) {
				if (!empty($slide['image'])) {
					@unlink($this->upload_dir.basename($slide['image']));
				}

				$this->model->deleteSlide($id_slide);

				$this->setHeader('Location', WRoute::getDir().'admin/slideshow');
				WNote::success('slide_deleted', WLang::get('The slide was successfully deleted.'));
			}

			return $slide;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/slideshow');
			WNote::error('slide_not_found', WLang::get('The slide was not found.'));
		}

		return array();
	}

	/**
	 * Reorders elements
	 *
	 * @return array WNote
	 */
	protected function slides_reorder(array $params) {
		if (WRequest::hasDataForURL('admin/slideshow/slides_reorder')) {
			$positions = WRequest::get('positions', null, 'POST');

			foreach ($positions as $id => $position) {
				$id = intval($id);

				if (!empty($id)) {
					$this->model->reorderSlide($id, intval($position));
				}
			}

			return WNote::success('reordering_success');
		} else {
			return WNote::error('data_missing');
		}
	}

	protected function configuration(array $params) {
		$config = $this->model->getConfig();

		$data = WRequest::getAssoc(array('update', 'config'));

		if ($data['update'] == 'true') {
			foreach ($config as $key => $value) {
				if ($key == 'autoplay') {
					$value = intval(isset($data['config']['autoplay']));

					$this->model->setConfig($key, $value);
				} else if (isset($data['config'][$key])) {
					$this->model->setConfig($key, $data['config'][$key]);
				}
			}

			// Refresh config
			$config = $this->model->getConfig();

			WNote::success('config_edited', WLang::get('The configuration was successfully saved.'));
			$this->setHeader('Location', WRoute::getDir().'admin/slideshow/configuration');
		}

		return $config;
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
