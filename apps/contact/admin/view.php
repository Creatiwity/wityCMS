<?php
/**
 * Contact Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * ContactAdminView is the Admin View of the Contact Application
 *
 * @package Apps\Contact\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class ContactAdminView extends WView {

	public function mail_history(array $model) {
		$this->assign('css', '/apps/contact/admin/css/contact.css');

		// SortingHelper Helper
		$sort = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());

		// Emails data
		foreach ($model['emails'] as $key => $email) {
			$msg = strip_tags($email['message']);
			$msg = preg_replace('/^\s+|\n|\r|\s+$/m', '', $msg);
			$model['emails'][$key]['abstract'] = $email['object'].'&nbsp;<span class="text-muted">-&nbsp;<span class="email-original">'.$msg.'</span></span>';
		}

		$this->assign('emails', $model['emails']);
		$this->assign('totalEmails', $model['totalEmails']);

		$pagination = WHelper::load('pagination', array(
			$model['totalEmails'],
			$model['users_per_page'],
			$model['current_page'],
			'/admin/contact/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'
		));
		$this->assign('pagination', $pagination->getHTML());
	}

	public function mail_detail(array $model) {
		$this->assign('id', $model['id']);
		$this->assign('from', $model['from']);
		$this->assign('object', $model['object']);
		$this->assign('message', $model['message']);
		$this->assign('attachment', $model['attachment']);
	}

	/**
	 * Prepares the config view
	 */
	public function config($config) {
		$this->assign('config', $config);
	}

}

?>
