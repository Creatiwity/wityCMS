<?php
/**
 * Contact Application - Front Controller - /apps/contact/front/main.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactController is the Front Controller of the Contact Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-02-10-2013
 */
class ContactController extends WController {

	protected function form(array $params) {
		$user_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
		
		if (!empty($_POST)) {
			$data = WRequest::getAssoc(array('from_name', 'from_company', 'from_email', 'email_object', 'email_message'));
			$errors = array();
			
			/**
			 * BEGING VARIABLES CHECKING
			 */
			if (empty($data['from_name'])) {
				$errors[] = WLang::get("no_from_name");
			} else if ($data['from_name'] test) {
				$errors[] = WLang::get("invalid_from_name");
			}
			
			if (!empty($data['from_company']) && !test) {
				$errors[] = WLang::get("article_no_author");
			}

			if (empty($data['from_email'])) {
				$errors[] = WLang::get("no_from_email");
			} else if ($data['from_email'] test) {
				$errors[] = WLang::get("invalid_from_email");
			}

			if (empty($data['email_object'])) {
				$errors[] = WLang::get("no_email_object");
			} else if ($data['email_object'] test) {
				$errors[] = WLang::get("invalid_email_object");
			}

			if (empty($data['email_message'])) {
				$errors[] = WLang::get("no_email_message");
			} else if ($data['email_message'] test) {
				$errors[] = WLang::get("invalid_email_message");
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
			
			if (empty($errors)) {
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
				}
			} else {
				WNote::error('data_errors', implode("<br />\n", $errors));
			}
		}
		
		// Load form
		$model = array(
			'from_name' => '',
			'from_email' => ''
		);
		
		if (!is_null($user_id)) { // Add name and email
			$model['from_name'] = $_SESSION['firstname'];
			$model['from_name'] = !empty($model['from_name']) ? $model['from_name'].' ' : $model['from_name']; // Add space after firstname
			$model['from_name'] .= $_SESSION['lastname'];
			
			$model['from_email'] = $_SESSION['email'];
		}

		return $model;
	}
	
}

?>