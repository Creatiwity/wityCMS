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
 * @version 0.5.0-dev-07-10-2014
 */
class PageAdminController extends WController {
	private $upload_dir = '/upload/page/';
	
	/**
	 * Handle page Listing action
	 */
	protected function listing(array $params) {
		$n = 40; // Items per page
		$sort_by = '';
		$sens = '';
		$page = 1;
		
		// Sorting criteria given in URL
		if (isset($params[0])) {
			$criterias = $params[0];
			sscanf(str_replace('-', ' ', $criterias), '%s %s %d', $sort_by, $sens, $crit);
			
			if ($crit > 1) {
				$page = $crit;
			}
		}
		
		// SortingHelper
		$orderingFields = array('id', 'title', 'author', 'date', 'modified_date', 'views', 'url');
		$sortingHelper = WHelper::load('SortingHelper', array($orderingFields, 'url', 'ASC'));
		$sorting = $sortingHelper->findSorting($sort_by, $sens);
		
		return array(
			'data'          => $this->model->getPageList(($page-1)*$n, $n, $sorting[0], $sorting[1] == 'ASC'),
			'total'         => $this->model->countPage(),
			'current_page'  => $page,
			'page_per_page' => $n,
			'sortingHelper' => $sortingHelper
		);
	}
	
	/**
	 * - Handles Add action
	 * - Prepares page form
	 */
	protected function form(array $params) {
		$page_id = isset($params[0]) ? intval($params[0]) : null;
		
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('author', 'meta_title', 'short_title', 'keywords', 'description', 'title', 'subtitle', 'url', 'content', 'parent'));
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
				function remove_accents($string) {
					$a = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ';
					$b = 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn';

					return strtr($string, $a, $b);
				}
				
				$data['url'] = strtolower(remove_accents($data['url']));
				$data['url'] = preg_replace('#[^a-zA-Z0-9\/\._-]+#', '-', $data['url']);
				$data['url'] = trim($data['url'], '-');
			}
			
			if (!empty($page_id)) {
				$db_data = $this->model->getPage($page_id);
			}
			
			// Image
			if (!empty($_FILES['image']['name'])) {
				$upload = WHelper::load('upload', array($_FILES['image']));
				$upload->file_new_name_body = preg_replace('#[^a-z0-9]#', '', strtolower($data['short_title']));
				$upload->file_overwrite = true;
				$upload->Process(WITY_PATH.$this->upload_dir);
				if (!$upload->processed) {
					$errors[] = $upload->error;
				} else {
					$data['image'] = $upload->file_dst_name;
					
					// Erase the previous image (careful to overwritten files)
					if (!empty($db_data['image']) && $db_data['image'] != $data['image']) {
						@unlink(WITY_PATH.$this->upload_dir.$db_data['image']);
					}
				}
			} else if (!empty($page_id)) {
				$data['image'] = $db_data['image'];
			} else {
				$data['image'] = '';
			}
			
			/* END VARIABLES CHECKING */
			
			if (empty($errors)) {
				if (is_null($page_id)) { // Add case
					if ($page_id = $this->model->createPage($data)) { 
						// Create custom route
						WRoute::defineCustom($data['url'], '/page/'.$page_id);
						
						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$page_id.'-'.$data['url']);
						return WNote::success('page_added', WLang::get('page_added', $data['title']));
					} else {
						WNote::error('page_not_added', WLang::get('page_not_added'));
					}
				} else { // Edit case
					$db_data = $this->model->getPage($page_id);
					
					if ($this->model->updatePage($page_id, $data)) {
						// Create custom route
						if ($db_data['url'] != $data['url']) {
							WRoute::deleteCustom($db_data['url']);
							WRoute::defineCustom($data['url'], '/page/'.$page_id);
						}
						
						$this->setHeader('Location', WRoute::getDir().'admin/page/edit/'.$page_id.'-'.$data['url']);
						return WNote::success('page_edited', WLang::get('page_edited', $data['title']));
					} else {
						WNote::error('page_not_edited', WLang::get('page_not_edited'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		// Load form
		$model = array(
			'id'    => $page_id, 
			'data'  => array(),
			'pages' => $this->model->getPages()
		);
		
		if (is_null($page_id)) { // Add case
			if (isset($data)) {
				$model['data'] = $data;
			}
		} else { // Edit case
			$model['data'] = $this->model->getPage($page_id);
		}
		
		return $model;
	}
	
	/**
	 * Handles Edit action
	 */
	protected function edit($params) {
		$page_id = isset($params[0]) ? intval($params[0]) : -1;
		
		// Check whether this page exists
		if ($this->model->validExistingPageId($page_id)) {
			return $this->form(array($page_id));
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/page');
			return WNote::error('page_not_found', WLang::get('page_not_found', $page_id));
		}
	}
	
	/**
	 * Handles page Delete action
	 * 
	 * @todo Handle properly the page_not_found case with Bootstrap
	 */
	protected function delete($params) {
		$page_id = isset($params[0]) ? intval($params[0]) : -1;
		
		if ($this->model->validExistingPageId($page_id)) {
			$data = $this->model->getPage($page_id);
			
			if (in_array('confirm', $params)) {
				$this->model->deletePage($page_id);
				
				if (!empty($data['image'])) {
					@unlink(WITY_PATH.$this->upload_dir.$data['image']);
				}
				
				// Delete custom route
				WRoute::deleteCustom($data['url']);
				
				// Treat child pages
				$this->model->removeParentPage($page_id);
				
				WNote::success('page_deleted', WLang::get('page_deleted', $data['title']));
				$this->setHeader('Location', WRoute::getDir().'admin/page');
			}
			
			return $data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/page');
			return WNote::error('page_not_found', WLang::get('page_not_found', $page_id));
		}
	}
}

?>
