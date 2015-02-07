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
 * @version 1.0.0-07-02-2015
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
			$lang_list = array(1);
			foreach ($translatable_fields as $field) {
				foreach ($lang_list as $i => $id_lang) {
					$value = WRequest::get($field.'_'.$id_lang);
					
					if (empty($value) && $i > 0) {
						// Use the value of the default lang
						$data_translatable[$id_lang][$field] = $data_translatable[$lang_list[0]][$field];
					} else {
						$data_translatable[$id_lang][$field] = $value;
					}
				}
			}
			
			/* BEGING VARIABLES CHECKING */
			if (empty($data_translatable[$lang_list[0]]['title'])) {
				$errors[] = WLang::get("no_title");
			}
			
			if (!empty($post_data['url']) && strpos($post_data['url'], '://') === false) {
				$post_data['url'] = 'http://'.$post_data['url'];
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
				} else {
					$post_data['image'] = '/upload/slideshow/'.$upload->file_dst_name;

					// Erase the previous image
					if (!empty($db_data['image'])) {
						@unlink($this->upload_dir.basename($db_data['image']));
					}
				}
			} else if (!empty($id_slide)) {
				$post_data['image'] = $db_data['image'];
			} else {
				$errors[] = "Veuillez fournir une image.";
			}

			if (empty($errors)) {
				if (empty($id_slide)) { // Add case
					$id_slide = $this->model->insertSlide($post_data, $data_translatable);

					if ($id_slide) {
						$this->setHeader('Location', WRoute::getDir().'admin/slideshow/edit/'.$id_slide);
						WNote::success('slide_added');

						$db_data = $this->model->getSlide($id_slide);
					} else {
						$db_data = $post_data;
						WNote::error('slide_not_added');
					}
				} else { // Edit case
					if ($this->model->updateSlide($id_slide, $post_data, $data_translatable)) {
						$this->setHeader('Location', WRoute::getDir().'admin/slideshow/edit/'.$id_slide);
						WNote::success('slide_edited');
					} else {
						$db_data = $post_data;
						WNote::error('slide_not_edited');
					}
				}
			} else {
				WNote::error('slide_data_error', implode("\n<br />", $errors));
				$db_data = $post_data;
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
			WNote::success('slide_not_found');
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
				WNote::success('slide_deleted');
			}

			return $slide;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/slideshow');
			WNote::success('slide_not_found');
		}

		return array();
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
			
			WNote::success('config_edited', WLang::get('config_edited'));
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
