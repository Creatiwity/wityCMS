<?php
/**
 * Newsletter Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterAdminController is the Admin Controller of the Newsletter Application
 *
 * @package Apps\Newsletter\Admin
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 0.6.2-04-06-2018
 */
class NewsletterAdminController extends WController {
	/**
	 * Handle subs Listing action
	 */
	protected function listing(array $params) {
		$n = 40; // Items per page
		$sort_by = '';
		$sens = '';
		$page = 1;

		// Sorting criteria given in URL
		if (isset($params[0])) {
			if (intval($params[0]) > 1) {
				$page = intval($params[0]);
			}
		}

		// SortingHelper
		$orderingFields = array('id', 'email', 'created_date');
		$sortingHelper = WHelper::load('SortingHelper', array($orderingFields, 'created_date', 'ASC'));
		$sorting = $sortingHelper->findSorting($sort_by, $sens);

		return array(
			'data'          => $this->model->getSubscribersList(($page-1)*$n, $n, $sorting[0], $sorting[1] == 'ASC'),
			'total'         => $this->model->countSubscribers(),
			'current_page'  => $page,
			'subs_per_page' => $n,
			'sortingHelper' => $sortingHelper
		);
	}

	/**
	 * Handles sub Delete action
	 */
	protected function delete($params) {
		$sub_id = isset($params[0]) ? intval($params[0]) : -1;

		if ($this->model->validExistingSubId($sub_id)) {
			$data = $this->model->getSubscriber($sub_id);

			if (in_array('confirm', $params)) {
				$this->model->deleteSubscriber($sub_id);

				WNote::success('sub_deleted', WLang::get('The subscriber &lt;strong&gt;%s&lt;/strong&gt; was successfully deleted.', $data['email']));
				$this->setHeader('Location', WRoute::getDir().'admin/newsletter');
			}

			return $data;
		} else {
			$this->setHeader('Location', WRoute::getDir().'admin/newsletter');
			return WNote::error('sub_not_found', WLang::get('Subscriber not found.', $sub_id));
		}
	}

	/**
	 * Handles export CSV action
	 */
	protected function export($params) {
		$this->model->exportCSV();
	}
}

?>
