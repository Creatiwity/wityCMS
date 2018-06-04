<?php
/**
 * Page Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * PageView is the Front View of the Page Application
 *
 * @package Apps\Page\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class PageView extends WView {
	public function display(array $model) {
		$this->assign('css', '/apps/page/front/css/page.css');

		$context = $this->getContext();

		if (!$context['parent']) {
			$this->tpl->assign('wity_page_title', $model['page']['meta_title'].' - '.WConfig::get('config.site_title'));
			$this->tpl->assign('wity_page_description', $model['page']['meta_description']);
		}

		$this->assign($model['page']);
	}
}

?>
