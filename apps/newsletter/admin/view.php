<?php
/**
 * Newsletter Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterAdminView is the Admin View of the Newsletter Application
 *
 * @package Apps\Newsletter\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class NewsletterAdminView extends WView {
	public function listing($model) {
		$sorting = $model['sortingHelper']->getSorting();
		$this->assign($model['sortingHelper']->getTplVars());

		$pagination = WHelper::load('pagination', array(
			$model['total'],
			$model['subs_per_page'],
			$model['current_page'],
			'/admin/newsletter/%d/'
		));
		$this->assign('pagination', $pagination->getHTML());

		$this->assign('subs', $model['data']);
	}

	public function delete($model) {
		$this->assign('email', $model['email']);
		$this->assign('confirm_delete_url', '/admin/newsletter/delete/'.$model['id'].'/confirm');
	}
}

?>
