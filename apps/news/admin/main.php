<?php
/**
 * News Application - Admin Controller
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminController is the Admin Controller of the News Application
 * 
 * @package Apps\News\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-19-04-2013
 */
class NewsAdminController extends WController {
	/**
	 * Handle News Listing action
	 */
	protected function listing(array $params) {
		$n = 30; // Rows per page
		$sort_by = '';
		$sens = '';
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
		$sortingHelper = WHelper::load('SortingHelper', array(
			array('news_id', 'news_title', 'news_author', 'news_date', 'news_views'), 
			'news_date', 'DESC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		return array(
			'data'         => $this->model->getNewsList(($page-1)*$n, $n, $sort[0], $sort[1] == 'ASC'),
			'total'        => $this->model->countNews(),
			'current_page' => $page,
			'per_page'     => $n,
			'sorting_vars' => $sort,
			'sorting_tpl'  => $sortingHelper->getTplVars()
		);
	}
	
	/**
	 * Form to add/edit a news.
	 * 
	 * @param int $news_id
	 * @param array $db_data
	 */
	private function news_form($news_id = 0, $db_data = array()) {
		$post_data = WRequest::getAssoc(array('news_author', 'news_meta_title', 'news_url'), null, 'POST');
		
		if (!in_array(null, $post_data, true)) {
			$post_data += WRequest::getAssoc(array('news_keywords', 'news_description', 'news_title', 'news_content', 'news_cats'), null, 'POST');
			$errors = array();
			
			/* BEGING VARIABLES CHECKING */
			if (empty($post_data['news_title'])) {
				$errors[] = WLang::get("article_no_title");
			}
			
			if (empty($post_data['news_author'])) {
				$errors[] = WLang::get("article_no_author");
			}
			
			// Treat custom news URL
			if (empty($post_data['news_url'])) {
				$errors[] = WLang::get("article_no_permalink");
			} else {
				$post_data['news_url'] = strtolower($post_data['news_url']);
				// remove accents
				$post_data['news_url'] = preg_replace('#[^a-z0-9.]+#', '-', $post_data['news_url']);
				$post_data['news_url'] = trim($post_data['news_url'], '-');
			}
			/* END VARIABLES CHECKING */
			
			if (empty($errors)) {
				if (empty($news_id)) { // Add case
					$news_id = $this->model->createNews($post_data);
					
					if ($news_id !== false) {
						// Treat categories
						if (!empty($post_data['news_cats'])) {
							foreach ($post_data['news_cats'] as $cat_id => $v) {
								$this->model->addCatToNews($news_id, intval($cat_id));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'admin/news/edit/'.$news_id.'-'.$post_data['news_url']);
						WNote::success('article_added', WLang::get('article_added', $post_data['news_title']));
					} else {
						WNote::error('article_not_added', WLang::get('article_not_added'));
					}
				} else { // Edit case
					if ($this->model->updateNews($news_id, $post_data)) {
						// Treat categories
						$this->model->removeCatsFromNews($news_id);
						if (!empty($post_data['news_cats'])) {
							foreach ($post_data['news_cats'] as $cat_id => $v) {
								$this->model->addCatToNews($news_id, intval($cat_id));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'admin/news/edit/'.$news_id.'-'.$post_data['news_url']);
						WNote::success('article_edited', WLang::get('article_edited', $post_data['news_title']));
					} else {
						WNote::error('article_not_edited', WLang::get('article_not_edited'));
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		return array(
			'data'      => $db_data,
			'post_data' => $post_data,
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
		
		$db_data = $this->model->getNews($news_id);
		
		if ($db_data !== false) {
			return $this->news_form($news_id, $db_data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/news');
			return WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
		}
	}
	
	/**
	 * Handles News Delete action
	 * 
	 * @todo Handle properly the article_not_found case with Bootstrap
	 */
	protected function news_delete($params) {
		$news_id = intval(array_shift($params));
		
		$db_data = $this->model->getNews($news_id);
		
		if ($db_data !== false) {
			if (in_array('confirm', $params)) {
				$this->model->removeCatsFromNews($news_id);
				$this->model->deleteNews($news_id);
				
				$this->setHeader('Location', WRoute::getDir().'admin/news');
				WNote::success('article_deleted', WLang::get('article_deleted', $db_data['news_title']));
			}
			
			return $db_data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/news');
			return WNote::error('article_not_found', WLang::get('article_not_found', $news_id));
		}
	}
	
	/**
	 * Handles News categories_manager action
	 */
	protected function categories_manager($params) {
		$post_data = WRequest::getAssoc(array('news_cat_name'), null, 'POST');
		
		// Data was sent by form
		if (!in_array(null, $post_data, true)) {
			$post_data += WRequest::getAssoc(array('news_cat_id', 'news_cat_shortname', 'news_cat_parent'));
			$errors = array();
			
			$cat_id = intval($post_data['news_cat_id']);
			
			if (empty($post_data['news_cat_name'])) {
				$errors[] = WLang::get('category_no_name');
			}
			
			// Format short name
			if (empty($post_data['news_cat_shortname'])) {
				$post_data['news_cat_shortname'] = strtolower($post_data['news_cat_name']);
			} else {
				$post_data['news_cat_shortname'] = strtolower($post_data['news_cat_shortname']);
			}
			$post_data['news_cat_shortname'] = preg_replace('#[^a-z0-9.]+#', '-', $post_data['news_cat_shortname']);
			$post_data['news_cat_shortname'] = trim($post_data['news_cat_shortname'], '-');
			
			if (empty($errors)) {
				if (empty($cat_id)) { // Add case
					if ($this->model->createCat($post_data)) {
						$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
						WNote::success('cat_added', WLang::get('cat_added', $post_data['news_cat_name']));
					} else {
						WNote::error('cat_not_added', WLang::get('cat_not_added'));
					}
				} else { // Edit case
					$db_data = $this->model->getCat($cat_id);
					
					// Check existing category
					if ($db_data !== false) {
						if ($this->model->updateCat($cat_id, $post_data)) {
							$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
							WNote::success('cat_edited', WLang::get('cat_edited', $post_data['news_cat_name']));
						} else {
							WNote::error('cat_not_edited', WLang::get('cat_not_edited'));
						}
					} else {
						WNote::error('cat_not_found', "The category you are trying to edit (#".$cat_id.") does not exist in the database.");
					}
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		// Sorting criteria given in URL
		$sort_by = '';
		$sens = '';
		if (!empty($params[0])) {
			sscanf(str_replace('-', ' ', $params[0]), '%s %s', $sort_by_crit, $sens);
			
			if (isset($this->model->cats_data_model['toDB'][$sort_by_crit])) {
				$sort_by = $sort_by_crit;
			}
		}
		
		// SortingHelper Helper
		$sortingHelper = WHelper::load('SortingHelper', array(
			array('news_cat_name', 'news_cat_shortname'), 
			'news_cat_name', 'ASC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		return array(
			'data'        => $this->model->getCatsList($sort[0], $sort[1]),
			'post_data'   => $post_data,
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
		
		$db_data = $this->model->getCat($cat_id);
		
		if ($db_data !== false) {
			if (in_array('confirm', $params)) {
				$this->model->removeCatsFromNews($cat_id);
				$this->model->unlinkChildrenOfParentCat($cat_id);
				$this->model->deleteCat($cat_id);
				
				$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
				WNote::success('category_deleted', WLang::get('category_deleted'));
			}
			
			return $db_data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
			return WNote::error('category_not_found', WLang::get('category_not_found'));
		}
	}
}

?>
