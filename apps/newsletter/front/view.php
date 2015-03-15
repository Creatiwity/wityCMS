<?php
/**
 * Newsletter Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsletterView is the Front View of Newsletter Page Application
 *
 * @package Apps\Newsletter\Front
 * @author Thibault Vlacich <thibault@vlacich.fr>
 * @version 1.0.0-01-03-2015
 */
class NewsletterView extends WView {
	public function add($model) {
		$this->assign('require', 'apps!newsletter/front');
	}
}

?>