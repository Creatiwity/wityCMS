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
 * @version 0.5.0-dev-23-03-2015
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
	protected function form($id_page = 0, $db_data = array()) {
		$data = array();

		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('title', 'content', 'url', 'meta_title', 'meta_description', 'author', 'subtitle', 'parent'));
			$errors = array();
			
			/* BEGING VARIABLES CHECKING */
			if (empty($data['title'])) {
				$errors[] = WLang::get('page_no_title');
			}
			
			if (empty($data['author'])) {
				$errors[] = WLang::get('page_no_author');
			}
			
			// Treat custom page URL
			if (empty($data['url'])) {
				$errors[] = WLang::get('page_no_permalink');
			} else {
				$data['url'] = strtolower(WTools::stripAccents($data['url']));
				$data['url'] = preg_replace('#[^a-zA-Z0-9\/\._-]+#', '-', $data['url']);
				$data['url'] = trim($data['url'], '-');
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
					$data['image'] = '/upload/page/'.$upload->file_dst_name;
					
					// Erase the previous image
					if (!empty($db_data['image'])) {
						@unlink($this->upload_dir.basename($db_data['image']));
					}
				}
			} else if (!empty($id_page)) {
				$data['image'] = $db_data['image'];
			} else {
				$data['image'] = '';
			}
			
			if (empty($errors)) {
				if (empty($id_page)) { // Add case
					if ($id_page = $this->model->createPage($data)) { 
						// Create custom route
						WRoute::defineCustom($data['url'], '/page/'.$id_page);
						
						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$id_page.'-'.$data['url']);
						return WNote::success('page_added', WLang::get('page_added', $data['title']));
					} else {
						WNote::error('page_not_added', WLang::get('page_not_added'));
					}
				} else { // Edit case
					if ($this->model->updatePage($id_page, $data)) {
						// Create custom route
						if ($db_data['url'] != $data['url']) {
							WRoute::deleteCustom($db_data['url']);
							WRoute::defineCustom($data['url'], '/page/'.$id_page);
						}
						
						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$id_page.'-'.$data['url']);
						return WNote::success('page_edited', WLang::get('page_edited', $data['title']));
					} else {
						WNote::error('page_not_edited', WLang::get('page_not_edited'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
				$db_data = $data;
			}
		}
		
		return array(
			'id'    => $id_page, 
			'data'  => $db_data,
			'pages' => $this->model->getPages()
		);
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
			return WNote::error('page_not_found', WLang::get('page_not_found'));
		}
	}
	
	/**
	 * Handles page Delete action
	 * 
	 * @todo Handle properly the page_not_found case with Bootstrap
	 */
	protected function delete($params) {
		$id_page = intval(array_shift($params));

		$db_data = $this->model->getPage($id_page);
		
		if (!empty($db_data)) {
			if (in_array('confirm', $params)) {
				$this->model->deletePage($id_page);
				
				if (!empty($db_data['image'])) {
					@unlink(WITY_PATH.$this->upload_dir.$db_data['image']);
				}
				
				// Delete custom route
				WRoute::deleteCustom($db_data['url']);
				
				// Treat child pages
				$this->model->removeParentPage($id_page);
				
				WNote::success('page_deleted', WLang::get('page_deleted', $db_data['title']));
				$this->setHeader('Location', WRoute::getDir().'admin/page');
			}
			
			return $db_data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/page');
			return WNote::error('page_not_found', WLang::get('page_not_found', $id_page));
		}
	}

	private function makeUploadDir() {
		if (!is_dir($this->upload_dir)) {
			mkdir($this->upload_dir, 0777, true);
		}
	}
}

?>
