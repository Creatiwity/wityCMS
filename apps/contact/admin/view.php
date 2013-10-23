<?php
/**
 * Contact Application - Admin View - /apps/contact/admin/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * ContactAdminView is the Admin View of the Contact Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4-07-10-2013
 */
class ContactAdminView extends WView {

	public function mail_history(array $model) {
		// SortingHelper Helper
		$sort = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());
		
		// Emails data
		foreach ($model['emails'] as $key => $email) {
			$model['emails'][$key]['abstract'] = $email['object'].'&nbsp;<span class="text-muted">-&nbsp;'.$email['message'].'</span>';
		}

		$this->assign('emails', $model['emails']);
		$this->assign('totalEmails', $model['totalEmails']);
		
		$pagination = WHelper::load('pagination', array($model['totalEmails'], $model['users_per_page'], $model['current_page'], '/admin/contact/'.$sort[0].'-'.strtolower($sort[1]).'-%d/'));
		$this->assign('pagination', $pagination->getHTML());
	}

	public function detail_mail(array $params) {

	}

}

?>
