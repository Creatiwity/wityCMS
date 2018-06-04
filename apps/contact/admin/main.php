<?php
/**
 * Contact Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * ContactAdminController is the Admin Controller of the Contact Application
 *
 * @package Apps\Contact\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class ContactAdminController extends WController {

	private $upload_dir;

	public function __construct() {
		$this->upload_dir = WITY_PATH.'upload'.DS.'contact'.DS;
	}

	/**
	 * Returns the corresponding mails (page and sorting) stored in the database
	 *
	 * @param array $params
	 * @return array List model
	 */
	protected function mail_history(array $params) {
		$n = 30; // number of emails per page

		// Sorting criterias given by URL
		$sort_by = '';
		$sens = 'DESC';
		$page = 1;
		if (!empty($params[0])) {
			$count = sscanf(str_replace('-', ' ', $params[0]), '%s %s %d', $sort_by, $sens, $page_input);
			if ($page_input > 1) {
				$page = $page_input;
			}
		}

		// SortingHelper
		$sortingHelper = WHelper::load('SortingHelper', array(array('id', 'from', 'name', 'organism', 'to', 'object', 'created_date'), 'created_date', 'DESC'));
		$sort = $sortingHelper->findSorting($sort_by, $sens);

		// Define model
		$model = array(
			'emails'         => $this->model->getEmailList(($page-1)*$n, $n, $sort[0], $sort[1] == 'ASC'),
			'totalEmails'    => $this->model->getEmailCount(),
			'current_page'   => $page,
			'users_per_page' => $n,
			'sortingHelper'  => $sortingHelper
		);

		return $model;
	}

	/**
	 * Retrieves a detail of a contact request.
	 *
	 * @param array $params Get parameters containing the request ID
	 * @return array Contact request model
	 */
	protected function mail_detail(array $params) {
		$id = intval(array_shift($params));
		if (empty($id)) {
			return WNote::error('missing_email_id', WLang::get('Email id is missing.'));
		}

		$model = $this->model->getEmail($id);
		if (!$model) {
			return WNote::error('not_found_email_id', WLang::get('Unable to find this email.'));
		}

		return $model;
	}

	protected function download(array $params) {
		$id = intval(array_shift($params));
		if (empty($id)) {
			return WNote::error('missing_email_id', WLang::get('Email id is missing.'));
		}

		$model = $this->model->getEmail($id);

		if (!empty($model)) {
			if (file_exists($model['attachment'])) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $model['attachment']);
				finfo_close($finfo);

				header('Content-Description: File Transfer');
				header('Content-Type: '.$mime);
				header('Content-Disposition: attachment; filename='.basename($model['attachment']));
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($model['attachment']));
				readfile($model['attachment']);
				exit;
			} else {
				$this->setHeader('Location', Wroute::getDir().'contact');
			}
		}

		return array();
	}

	/**
	 * Configuration handler
	 *
	 * @return array Config model
	 */
	protected function config() {
		$data = WRequest::getAssoc(array('update', 'config'));
		$config = $this->model->getConfig();

		if ($data['update'] == 'true') {
			foreach ($config as $name => $value) {
				if (isset($data['config'][$name])) {
					$config[$name] = $data['config'][$name];

					$this->model->setConfig($name, $config[$name]);
				}
			}

			WNote::success('contact_config_updated', WLang::get('The configuration was updated.'));
		}

		return $config;
	}

}

?>
