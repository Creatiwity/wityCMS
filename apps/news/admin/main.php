<?php
/**
 * News Application - Admin Controller - /apps/news/admin/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminController is the Admin Controller of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class NewsAdminController extends WController {
	public function __construct() {
		include 'model.php';
		$this->model = new NewsAdminModel();
		
		include 'view.php';
		$this->setView(new NewsAdminView());
	}
	
	/**
	 * Get the Id given in URL
	 */
	private function getId() {
		$args = WRoute::getArgs();
		if (empty($args[1])) {
			return null;
		} else {
			list ($id) = explode('-', $args[1]);
			return intval($id);
		}
	}
	
	/**
	 * Handle News Listing action
	 */
	protected function listing() {
		$n = 30; // Number of news per page
		
		// Sorting criterias given by URL
		$args = WRoute::getArgs();
		$criterias = array_shift($args);
		if ($criterias == 'listing') {
			$criterias = array_shift($args);
		}
		$count = sscanf(str_replace('-', ' ', $criterias), '%s %s %d', $sortBy, $sens, $page);
		if (!isset($this->model->news_data_model['toDB'][$sortBy])) {
			$sortBy = 'news_date';
		}
		if (empty($page) || $page <= 0) {
			$page = 1;
		}
		
		// AdminStyle Helper
		$orderingFields = array('news_id', 'news_title', 'news_author', 'news_date', 'news_views');
		$adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'news_date', 'DESC'));
		$sorting = $adminStyle->findSorting($sortBy, $sens);
		
		return array(
			'data' => $this->model->getNewsList(($page-1)*$n, $n, $sorting[0], $sorting[1] == 'ASC'),
			'total' => $this->model->countNews(),
			'current_page' => $page,
			'news_per_page' => $n,
			'adminStyle' => $adminStyle
		);
	}
	
	/**
	 * - Handles Add action
	 * - Prepares News form
	 */
	protected function news_form($news_id = null) {
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('news_author', 'news_keywords', 'news_title', 'news_url', 'news_content', 'news_cats'));
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
				$data['news_url'] = preg_replace('#[^a-z0-9.]#', '-', $data['news_url']);
				$data['news_url'] = preg_replace('#-{2,}#', '-', $data['news_url']);
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
			
			if (!empty($errors)) {
				WNote::error('data_errors', implode("<br />\n", $errors));
			} else {
				if (is_null($news_id)) { // Add case
					if ($this->model->createNews($data)) {
						$news_id = $this->model->getLastNewsId();
						
						// Treat categories
						if (!empty($data['news_cats'])) {
							foreach ($data['news_cats'] as $cat_id => $v) {
								$this->model->addCatToNews($news_id, intval($cat_id));
							}
						}
						
						WNote::success('article_added', WLang::get('article_added', $data['news_title']));
						$this->view->setHeader('Location', Wroute::getDir().'/admin/news/edit/'.$news_id.'-'.$data['news_url']);
						return;
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
						
						WNote::success('article_edited', WLang::get('article_edited', $data['news_title']));
						$this->view->setHeader('Location', Wroute::getDir().'/admin/news/edit/'.$news_id.'-'.$data['news_url']);
						return;
					} else {
						WNote::error('article_not_edited', WLang::get('article_not_edited'));
					}
				}
			}
		}
		
		// Load form
		$model = array('news_id' => '', 'data' => array(), 'cats_list' => array());
		$cats_list = $this->model->getCatsList('news_cat_name', 'ASC');
		$model['cats_list'] = $cats_list;
		if (is_null($news_id)) { // Add case
			$model['news_id'] = $this->model->getLastNewsId() + 1;
			if (isset($data)) {
				$model['data'] = $data;
			}
		} else { // Edit case
			$model['news_id'] = $news_id;
			$model['data'] = $this->model->getNews($news_id);
		}
		return $model;
	}
	
	/**
	 * Handles Edit action
	 */
	protected function edit() {
		$news_id = $this->getId();
		
		// Check whether this news exist
		if (empty($news_id) || !$this->model->validExistingNewsId($news_id)) {
			WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
			header('Location: '.WRoute::getDir().'/admin/news/');
		} else {
			return $this->news_form($news_id);
		}
	}
	
	/**
	 * Handles News Delete action
	 * 
	 * @todo Handle properly the article_not_found case with Bootstrap
	 */
	protected function news_delete() {
		$news_id = $this->getId();
		if ($this->model->validExistingNewsId($news_id)) {
			$data = $this->model->getNews($news_id);
			$args = WRoute::getArgs();
			if (isset($args[2]) && $args[2] == "confirm") {
				$this->model->removeCatsFromNews($news_id);
				$this->model->deleteNews($news_id);
				WNote::success('article_deleted', WLang::get('article_deleted', $data['news_title']));
				$this->view->setHeader('Location', WRoute::getDir() . '/admin/news/');
			}
			return $data;
		} else {
			WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
			$this->view->setHeader('Location', WRoute::getDir() . '/admin/news/');
		}
	}
	
	/**
	 * Handles News categories_manager action
	 */
	protected function categories_manager() {
		// Sorting criterias given by URL
		$args = WRoute::getArgs();
		$criterias = array_shift($args);
		if ($criterias == 'listing') {
			$criterias = array_shift($args);
		}
		$count = sscanf(str_replace('-', ' ', $criterias), '%s %s', $sortBy, $sens);
		if (!isset($this->model->cats_data_model['toDB'][$sortBy])) {
			$sortBy = 'news_cat_name';
		}
		
		// Data was sent by form
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('news_cat_id', 'news_cat_name', 'news_cat_shortname', 'news_cat_parent'));
			$cat_id = intval($data['news_cat_id']);
			$errors = array();
			
			// Check existing category
			if (!empty($cat_id) && !$this->model->validExistingCatId($cat_id)) {
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
			$data['news_cat_shortname'] = preg_replace('#[^a-z0-9.]#', '-', $data['news_cat_shortname']);
			$data['news_cat_shortname'] = preg_replace('#-{2,}#', '-', $data['news_cat_shortname']);
			$data['news_cat_shortname'] = trim($data['news_cat_shortname'], '-');
			
			if (!empty($errors)) {
				WNote::error('data_errors', implode("<br />\n", $errors), 'assign');
			} else {
				if (empty($cat_id)) { // Add case
					if ($this->model->createCat($data)) {
						WNote::success('cat_added', WLang::get('cat_added', $data['news_cat_name']));
						header('Location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
						return;
					} else {
						WNote::error('cat_not_added', WLang::get('cat_not_added'));
					}
				} else { // Edit case
					if ($this->model->updateCat($cat_id, $data)) {
						WNote::success('cat_edited', WLang::get('cat_edited', $data['news_cat_name']));
						header('Location: ' . WRoute::getDir() . '/admin/news/categories_manager/');
						return;
					} else {
						WNote::error('cat_not_edited', WLang::get('cat_not_edited'));
					}
				}
			}
		}
		
		// AdminStyle Helper
		$orderingFields = array('news_cat_name', 'news_cat_shortname');
		$adminStyle = WHelper::load('SortingHelper', array($orderingFields, 'news_cat_name', 'ASC'));
		$sorting = $adminStyle->findSorting($sortBy, $sens);
		
		$model = array(
			'data' => $this->model->getCatsList($sorting[0], $sorting[1]),
			'adminStyle' => $adminStyle,
			'post_data' => array()
		);
		if (isset($data)) {
			$model['post_data'] = $data;
		}
		return $model;
	}
	
	/**
	 * Handles Category_delete action
	 * 
	 * @todo Handle properly the cat_not_found case with Bootstrap
	 */
	protected function category_delete() {
		$cat_id = $this->getId();
		if ($this->model->validExistingCatId($cat_id)) {
			$args = WRoute::getArgs();
			if (isset($args[2]) && $args[2] == "confirm") {
				$this->model->removeCatsFromNews($cat_id);
				$this->model->unlinkChildrenOfParentCat($cat_id);
				$this->model->deleteCat($cat_id);
				WNote::success('category_deleted', WLang::get('category_deleted'));
				$this->view->setHeader('Location', WRoute::getDir() . '/admin/news/categories_manager/');
			}
			return $cat_id;
		} else {
			WNote::error('category_not_found', WLang::get('category_not_found'));
		}
	}
}

?>
