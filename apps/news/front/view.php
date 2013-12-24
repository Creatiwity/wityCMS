<?php
/**
 * News Application - Front View
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsView is the Front View of the News Application
 * 
 * @package Apps\News\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0-19-04-2013
 */
class NewsView extends WView {
	public function listing($data) {
		$this->assign('news', $data);
	}
}

?>