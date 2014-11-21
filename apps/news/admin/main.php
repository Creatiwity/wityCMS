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
	private $upload_dir = '/upload/news/';
	
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
			
			if (in_array($sort_by_crit, array('title', 'author', 'created_date', 'views'))) {
				$sort_by = $sort_by_crit;
			}
			
			if ($page_crit > 1) {
				$page = $page_crit;
			}
		}
		
		// SortingHelper
		$sortingHelper = WHelper::load('SortingHelper', array(
			array('id', 'title', 'author', 'created_date', 'views'), 
			'created_date', 'DESC'
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
	 * @param int $id_news
	 * @param array $db_data
	 */
	private function news_form($id_news = 0, $db_data = array()) {
		$post_data = WRequest::getAssoc(array('url'), null, 'POST');
		
		if (!in_array(null, $post_data, true)) {
			$errors = array();
			$post_data += WRequest::getAssoc(array('cats', 'author', 'meta_title', 'keywords', 'description', 'title', 'content', 'published'));
			
			/* BEGING VARIABLES CHECKING */
			if (empty($post_data['title'])) {
				$errors[] = WLang::get("article_no_title");
			}
			
			if (empty($post_data['author'])) {
				$errors[] = WLang::get("article_no_author");
			}
			
			// Treat custom news URL
			if (empty($post_data['url'])) {
				$errors[] = WLang::get("article_no_permalink");
			} else {
				$post_data['url'] = strtolower($post_data['url']);
				// remove accents
				$post_data['url'] = preg_replace('#[^a-z0-9.]+#', '-', $post_data['url']);
				$post_data['url'] = trim($post_data['url'], '-');
			}
			
			$post_data['published'] = ($post_data['published'] == 'on') ? 1 : 0;
			/* END VARIABLES CHECKING */
			
			// Image
			if (!empty($_FILES['image']['name'])) {
				$upload = WHelper::load('upload', array($_FILES['image']));
				$upload->file_new_name_body = preg_replace('#[^a-z0-9_-]#', '', strtolower($post_data['title'])).time();
				$upload->file_overwrite = true;
				$upload->Process(WITY_PATH.$this->upload_dir);
				if (!$upload->processed) {
					$errors[] = $upload->error;
				} else {
					$post_data['image'] = $upload->file_dst_name;
					
					// Erase the previous image (careful to overwritten files)
					if (!empty($db_data['image']) && $db_data['image'] != $post_data['image']) {
						@unlink(WITY_PATH.$this->upload_dir.$db_data['image']);
					}
				}
			} else if (!empty($id_news)) {
				$post_data['image'] = $db_data['image'];
			} else {
				$post_data['image'] = '';
			}
			
			if (empty($errors)) {
				if (empty($id_news)) { // Add case
					$id_news = $this->model->createNews($post_data);
					
					if ($id_news !== false) {
						// Treat categories
						if (!empty($post_data['cats'])) {
							foreach ($post_data['cats'] as $id_cat => $v) {
								$this->model->addCatToNews($id_news, intval($id_cat));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'admin/news/edit/'.$id_news.'-'.$post_data['url']);
						WNote::success('article_added', WLang::get('article_added', $post_data['title']));
					} else {
						WNote::error('article_not_added', WLang::get('article_not_added'));
					}
				} else { // Edit case
					if ($this->model->updateNews($id_news, $post_data)) {
						// Treat categories
						$this->model->removeCatsFromNews($id_news);
						if (!empty($post_data['cats'])) {
							foreach ($post_data['cats'] as $id_cat => $v) {
								$this->model->addCatToNews($id_news, intval($id_cat));
							}
						}
						
						$this->setHeader('Location', Wroute::getDir().'admin/news/edit/'.$id_news.'-'.$post_data['url']);
						WNote::success('article_edited', WLang::get('article_edited', $post_data['title']));
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
			'cats'      => $this->model->getCatsList('name', 'ASC')
		);
	}
	
	protected function add($params) {
		return $this->news_form();
	}
	
	/**
	 * Handles Edit action
	 */
	protected function edit($params) {
		$id_news = intval(array_shift($params));
		
		$db_data = $this->model->getNews($id_news);
		
		if ($db_data !== false) {
			return $this->news_form($id_news, $db_data);
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/news');
			return WNote::error('article_not_found', WLang::get('article_not_found', $id_news));
		}
	}
	
	/**
	 * Handles News Delete action
	 * 
	 * @todo Handle properly the article_not_found case with Bootstrap
	 */
	protected function news_delete($params) {
		$id_news = intval(array_shift($params));
		
		$db_data = $this->model->getNews($id_news);
		
		if ($db_data !== false) {
			if (in_array('confirm', $params)) {
				if (!empty($db_data['image'])) {
					@unlink(WITY_PATH.$this->upload_dir.$data['image']);
				}
				
				$this->model->removeCatsFromNews($id_news);
				$this->model->deleteNews($id_news);
				
				$this->setHeader('Location', WRoute::getDir().'admin/news');
				WNote::success('article_deleted', WLang::get('article_deleted', $db_data['title']));
			}
			
			return $db_data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/news');
			return WNote::error('article_not_found', WLang::get('article_not_found', $id_news));
		}
	}
	
	/**
	 * Handles News categories_manager action
	 */
	protected function categories_manager($params) {
		$post_data = WRequest::getAssoc(array('name'), null, 'POST');
		
		// Data was sent by form
		if (!in_array(null, $post_data, true)) {
			$post_data += WRequest::getAssoc(array('id', 'shortname', 'parent'));
			$errors = array();
			
			$id_cat = intval($post_data['id']);
			
			if (empty($post_data['name'])) {
				$errors[] = WLang::get('category_no_name');
			}
			
			// Format short name
			if (empty($post_data['shortname'])) {
				$post_data['shortname'] = strtolower($post_data['name']);
			} else {
				$post_data['shortname'] = strtolower($post_data['shortname']);
			}
			$post_data['shortname'] = preg_replace('#[^a-z0-9.]+#', '-', $post_data['shortname']);
			$post_data['shortname'] = trim($post_data['shortname'], '-');
			
			if (empty($errors)) {
				if (empty($id_cat)) { // Add case
					if ($this->model->createCat($post_data)) {
						$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
						WNote::success('cat_added', WLang::get('cat_added', $post_data['name']));
					} else {
						WNote::error('cat_not_added', WLang::get('cat_not_added'));
					}
				} else { // Edit case
					$db_data = $this->model->getCat($id_cat);
					
					// Check existing category
					if ($db_data !== false) {
						if ($this->model->updateCat($id_cat, $post_data)) {
							$this->setHeader('Location', WRoute::getDir().'admin/news/categories_manager');
							WNote::success('cat_edited', WLang::get('cat_edited', $post_data['name']));
						} else {
							WNote::error('cat_not_edited', WLang::get('cat_not_edited'));
						}
					} else {
						WNote::error('cat_not_found', "The category you are trying to edit (#".$id_cat.") does not exist in the database.");
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
			array('name', 'shortname'), 
			'name', 'ASC'
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
		$id_cat = intval(array_shift($params));
		
		$db_data = $this->model->getCat($id_cat);
		
		if ($db_data !== false) {
			if (in_array('confirm', $params)) {
				$this->model->removeCatsFromNews($id_cat);
				$this->model->unlinkChildrenOfParentCat($id_cat);
				$this->model->deleteCat($id_cat);
				
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
