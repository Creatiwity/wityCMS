<?php
/**
 * Page Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageAdminController is the Admin Controller of the Page Application
 *
 * @package Apps\Page\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class PageAdminController extends WController {
	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'page'.DS;
	}

	/**
	 * Handle page Listing action
	 */
	protected function pages(array $params) {
		$n = 30; // Items per page
		$page = 1;

		if (!empty($params[0])) {
			$page_params = intval($params[0]);

			if ($page_params > 1) {
				$page = $page_params;
			}
		}

		return array(
			'data'          => $this->model->getPages(($page-1)*$n, $n),
			'total'         => $this->model->countPages(),
			'current_page'  => $page,
			'page_per_page' => $n,
		);
	}

	/**
	 * - Handles Add action
	 * - Prepares page form
	 */
	private function form($id_page = 0, $db_data = array()) {
		if (WRequest::getMethod() == 'POST') {
			$errors = array();
			$post_data = WRequest::getAssoc(array('parent'));
			$data_translatable = array();

			// Format translatable fields
			$translatable_fields = array('title', 'subtitle', 'content', 'author', 'url', 'meta_title', 'meta_description');
			$lang_list = WLang::getLangIds();
			$default_id = WLang::getDefaultLangId();

			foreach ($translatable_fields as $field) {
				foreach ($lang_list as $i => $id_lang) {
					$value = WRequest::get($field.'_'.$id_lang);

					if ($field == 'url') {
						$value = strtolower(WTools::stripAccents($value));
						$value = preg_replace('#[^a-zA-Z0-9\/\._-]+#', '-', $value);
						$value = trim($value, '-');
					}

					if (($value === null || $value === '') && $id_lang != $default_id) {
						// Use the value of the default lang
						$data_translatable[$id_lang][$field] = $data_translatable[$default_id][$field];
					} else {
						$data_translatable[$id_lang][$field] = $value;
					}
				}
			}

			/* BEGING VARIABLES CHECKING */
			if (empty($data_translatable[$lang_list[0]]['title'])) {
				$errors[] = WLang::get('Please, provide a title.');
			}

			// Treat custom page URL
			if (empty($data_translatable[$lang_list[0]]['url'])) {
				$errors[] = WLang::get('Please, provide a URL.');
			}

			$post_data['menu'] = false;
			/* END VARIABLES CHECKING */

			// Image
			if (!empty($_FILES['image']['name'])) {
				$this->makeUploadDir();

				$upload = WHelper::load('upload', array($_FILES['image']));
				$upload->allowed = array('image/*');

				$upload->Process($this->upload_dir);

				if (!$upload->processed) {
					$errors[] = $upload->error;
					$post_data['image'] = $db_data['image'];
				} else {
					$post_data['image'] = '/upload/page/'.$upload->file_dst_name;

					// Erase the previous image
					if (!empty($db_data['image'])) {
						@unlink($this->upload_dir.basename($db_data['image']));
					}
				}
			} else if (!empty($id_page)) {
				$post_data['image'] = $db_data['image'];
			} else {
				$post_data['image'] = '';
			}

			if (empty($errors)) {
				if (empty($id_page)) { // Add case
					if ($id_page = $this->model->createPage($post_data, $data_translatable)) {
						// Create custom route
						foreach ($lang_list as $i => $id_lang) {
							if (!empty($data_translatable[$id_lang]['url'])) {
								WRoute::defineCustom($data_translatable[$id_lang]['url'], '/page/'.$id_page);
							}
						}

						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$id_page.'-'.$data_translatable[$lang_list[0]]['url']);
						return WNote::success('page_added', WLang::get('The page <strong>%s</strong> was successfully created.', $data_translatable[$lang_list[0]]['title']));
					} else {
						WNote::error('page_not_added', WLang::get('An unknown error occurred while creating the page in the database.'));
					}
				} else { // Edit case
					if ($this->model->updatePage($id_page, $post_data, $data_translatable)) {
						// Create custom route
						foreach ($lang_list as $i => $id_lang) {
							if ($db_data['url_'.$id_lang] != $data_translatable[$id_lang]['url']) {
								WRoute::deleteCustom($db_data['url_'.$id_lang]);
								WRoute::defineCustom($data_translatable[$id_lang]['url'], '/page/'.$id_page);
							}
						}

						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$id_page.'-'.$data_translatable[$lang_list[0]]['url']);
						return WNote::success('page_edited', WLang::get('The page <strong>%s</strong> was successfully edited.', $data_translatable[$lang_list[0]]['title']));
					} else {
						WNote::error('page_not_edited', WLang::get('An unknown error occurred while editing the page in the database.'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
				$db_data = $post_data;
			}
		}

		return array(
			'id'    => $id_page,
			'data'  => $db_data,
			'pages' => $this->model->getPages()
		);
	}

	/**
	 * Handles Add action
	 */
	protected function add($params) {
		return $this->form();
	}

	/**
	 * Handles Edit action
	 */
	protected function edit($params) {
		$id_page = intval(array_shift($params));

		$db_data = $this->model->getPage($id_page);

		// Check whether this page exists
		if (!empty($db_data)) {
			return $this->form($id_page, $db_data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/page');
			return WNote::error('page_not_found', WLang::get('Page not found.'));
		}
	}

	/**
	 * Handles page Delete action
	 */
	protected function delete($params) {
		$id_page = intval(array_shift($params));

		$db_data = $this->model->getPage($id_page);

		if (!empty($db_data)) {
			if (in_array('confirm', $params)) {
				$this->model->deletePage($id_page);

				if (!empty($db_data['image'])) {
					@unlink($this->upload_dir.$db_data['image']);
				}

				// Delete custom route
				$lang_list = array(1, 2);
				foreach ($lang_list as $i => $id_lang) {
					WRoute::deleteCustom($db_data['url_'.$id_lang]);
				}

				// Treat child pages
				$this->model->removeParentPage($id_page);

				WNote::success('page_deleted', WLang::get('The page <strong>%s</strong> was successfully deleted.', $db_data['title_1']));
				$this->setHeader('Location', WRoute::getDir().'admin/page');
			}

			return $db_data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/page');
			return WNote::error('page_not_found', WLang::get('Page not found.', $id_page));
		}
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
