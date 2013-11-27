<?php
/**
 * News Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsAdminController is the Admin Controller of the News Application
 * 
 * @package Apps\News\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-19-04-2013
 */
class NewsAdminController extends WController {
	/**
	 * Handle News Listing action
	 */
	protected function listing(array $params) {
		$n = 30; // Number of news per page
		$sort_by = 'news_date';
		$sens = 'DESC';
		$page = 1;
		
		// Sorting criterias given by URL
		if (isset($params[0])) {
			$criterias = $params[0];
			sscanf(str_replace('-', ' ', $criterias), '%s %s %d', $sort_by_crit, $sens, $page_crit);
			
			if (isset($this->model->news_data_model['toDB'][$sort_by_crit])) {
				$sort_by = $sort_by_crit;
			}
			
			if ($page_crit > 1) {
				$page = $page_crit;
			}
		}
		
		// SortingHelper
		$orderingFields = array('news_id', 'news_title', 'news_author', 'news_date', 'news_views');
		$sortingHelper = WHelper::load('SortingHelper', array($orderingFields, 'news_date', 'DESC'));
		$sorting = $sortingHelper->findSorting($sort_by, $sens);
		
		return array(
			'data'         => $this->model->getNewsList(($page-1)*$n, $n, $sorting[0], $sorting[1] == 'ASC'),
			'total'        => $this->model->countNews(),
			'current_page' => $page,
			'per_page'     => $n,
			'sorting'      => $sorting,
			'sorting_tpl'  => $sortingHelper->getTplVars()
		);
	}
	
	/**
	 * 
	 */
	private function news_form($news_id = null, $db_data = array()) {
		$data = array();
		
		if (WRequest::hasData()) {
			$data = WRequest::getAssoc(array('news_author', 'news_meta_title', 'news_keywords', 'news_description', 'news_title', 'news_url', 'news_content', 'news_cats'));
			$errors = array();
			
			/**
			 * BEGING VARIABLES CHECKING
			 */
			if (empty($data['news_title'])) {
				$errors[] = WLang::get("article_no_title");
			}
			
			if (empty($data['news_author'])) {
				$errors[] = WLang::get("article_no_author");
			}
			
			// Treat custom news URL
			if (empty($data['news_url'])) {
				$errors[] = WLang::get("article_no_permalink");
			} else {
				$data['news_url'] = strtolower($data['news_url']);
				// remove accents
				$data['news_url'] = preg_replace('#[^a-z0-9.]+#', '-', $data['news_url']);
				$data['news_url'] = trim($data['news_url'], '-');
			}
			/**
			 * END VARIABLES CHECKING
			 */
			
			// Image on front page
			// if (!empty($_FILES['news_image']['name'])) {
				// include HELPERS_DIR . 'upload/upload.php';
				// $upload = new Upload($_FILES['news_image']);
				// $upload->file_new_name_body = preg_replace('#[^a-z0-9]#', '', strtolower($data['news_title']));
				// $upload->file_overwrite = true;
				// $upload->Process(WT_PATH . 'upload/news/');
				// if (!$upload->processed) {
					// $errors[] = WLang::get('article_image_error', $upload->error);
				// }
				// $data['news_image'] = $upload->file_dst_name;
			// } else {
				// $data['news_image'] = '';
			// }
			
			if (empty($errors)) {
				if (empty($news_id)) { // Add case
					$news_id = $this->model->createNews($data);
					if ($news_id !== false) {
						// Treat categories
						if (!empty($data['news_cats'])) {
							foreach ($data['news_cats'] as $cat_id => $v) {
								$this->model->addCatToNews($news_id, intval($cat_id));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'/admin/news/edit/'.$news_id.'-'.$data['news_url']);
						return WNote::success('article_added', WLang::get('article_added', $data['news_title']));
					} else {
						WNote::error('article_not_added', WLang::get('article_not_added'));
					}
				} else { // Edit case
					if ($this->model->updateNews($news_id, $data)) {
						// Treat categories
						$this->model->removeCatsFromNews($news_id);
						if (!empty($data['news_cats'])) {
							foreach ($data['news_cats'] as $cat_id => $v) {
								$this->model->addCatToNews($news_id, intval($cat_id));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'/admin/news/edit/'.$news_id.'-'.$data['news_url']);
						return WNote::success('article_edited', WLang::get('article_edited', $data['news_title']));
					} else {
						WNote::error('article_not_edited', WLang::get('article_not_edited'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		return array(
			'news_id'   => $news_id,
			'last_id'   => empty($news_id) ? '1' : $news_id,
			'data'      => $db_data,
			'post_data' => $data,
			'cats'      => $this->model->getCatsList('news_cat_name', 'ASC')
		);
	}
	
	protected function add($params) {
		return $this->news_form();
	}
	
	/**
	 * Handles Edit action
	 */
	protected function edit($params) {
		$news_id = intval(array_shift($params));
		
		if (!$this->model->validExistingNewsId($news_id)) {
			$this->setHeader('Location', WRoute::getDir().'/admin/news/');
			return WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
		}
		
		$news_data = $this->model->getNews($news_id);
		return $this->news_form($news_id, $news_data);
	}
	
	/**
	 * Handles News Delete action
	 * 
	 * @todo Handle properly the article_not_found case with Bootstrap
	 */
	protected function news_delete($params) {
		$news_id = intval(array_shift($params));
		
		if (!$this->model->validExistingNewsId($news_id)) {
			$this->setHeader('Location', WRoute::getDir() . '/admin/news/');
			return WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
		}
		
		$data = $this->model->getNews($news_id);
		
		if (in_array('confirm', $params)) {
			$this->model->removeCatsFromNews($news_id);
			$this->model->deleteNews($news_id);
			
			$this->setHeader('Location', WRoute::getDir() . '/admin/news/');
			WNote::success('article_deleted', WLang::get('article_deleted', $data['news_title']));
		}
		
		return $data;
	}
	
	/**
	 * Handles News categories_manager action
	 */
	protected function categories_manager($params) {
		$data = array();
		
		// Data was sent by form
		if (WRequest::hasData()) {
			$data = WRequest::getAssoc(array('news_cat_id', 'news_cat_name', 'news_cat_shortname', 'news_cat_parent'));
			$cat_id = intval($data['news_cat_id']);
			$errors = array();
			
			// Check existing category
			if (!$this->model->validExistingCatId($cat_id)) {
				$errors[] = "The category you are trying to edit (#".$data['news_cat_id'].") does not exist in the database.";
			}
			
			if (empty($data['news_cat_name'])) {
				$errors[] = WLang::get('category_no_name');
			}
			
			// Format short name
			if (empty($data['news_cat_shortname'])) {
				$data['news_cat_shortname'] = strtolower($data['news_cat_name']);
			} else {
				$data['news_cat_shortname'] = strtolower($data['news_cat_shortname']);
			}
			$data['news_cat_shortname'] = preg_replace('#[^a-z0-9.]+#', '-', $data['news_cat_shortname']);
			$data['news_cat_shortname'] = trim($data['news_cat_shortname'], '-');
			
			if (empty($errors)) {
				if (empty($cat_id)) { // Add case
					if ($this->model->createCat($data)) {
						$this->setHeader('Location', WRoute::getDir() . '/admin/news/categories_manager/');
						WNote::success('cat_added', WLang::get('cat_added', $data['news_cat_name']));
					} else {
						WNote::error('cat_not_added', WLang::get('cat_not_added'));
					}
				} else { // Edit case
					if ($this->model->updateCat($cat_id, $data)) {
						$this->setHeader('Location', WRoute::getDir() . '/admin/news/categories_manager/');
						WNote::success('cat_edited', WLang::get('cat_edited', $data['news_cat_name']));
					} else {
						WNote::error('cat_not_edited', WLang::get('cat_not_edited'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		// Sorting criteria given in URL
		$sort_by = 'news_cat_name';
		$sens = 'ASC';
		if (!empty($params[0])) {
			sscanf(str_replace('-', ' ', $params[0]), '%s %s', $sort_by_crit, $sens);
			
			if (isset($this->model->cats_data_model['toDB'][$sort_by_crit])) {
				$sort_by = $sort_by_crit;
			}
		}
		
		// AdminStyle Helper
		$orderingFields = array('news_cat_name', 'news_cat_shortname');
		$sortingHelper = WHelper::load('SortingHelper', array($orderingFields, 'news_cat_name', 'ASC'));
		$sorting = $sortingHelper->findSorting($sort_by, $sens);
		
		return array(
			'data'        => $this->model->getCatsList($sorting[0], $sorting[1] == 'ASC'),
			'post_data'   => $data,
			'sorting_tpl' => $sortingHelper->getTplVars()
		);
	}
	
	/**
	 * Handles Category_delete action
	 * 
	 * @todo Handle properly the cat_not_found case with Bootstrap
	 */
	protected function category_delete($params) {
		$cat_id = intval(array_shift($params));
		
		if (!$this->model->validExistingCatId($cat_id)) {
			return WNote::error('category_not_found', WLang::get('category_not_found'));
		}
		
		if (in_array('confirm', $params)) {
			$this->model->removeCatsFromNews($cat_id);
			$this->model->unlinkChildrenOfParentCat($cat_id);
			$this->model->deleteCat($cat_id);
			
			$this->setHeader('Location', WRoute::getDir() . '/admin/news/categories_manager/');
			WNote::success('category_deleted', WLang::get('category_deleted'));
		}
		
		return array('cat_id' => $cat_id);
	}
}

?>
