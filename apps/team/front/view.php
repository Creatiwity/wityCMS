<?php
/**
 * Team Application - Front View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * TeamView is the  View of the Team Application.
 *
 * @package Apps\Team\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class TeamView extends WView {
	public function members(array $model) {
		$this->assign('css', '/apps/team/front/css/team.css');

		$this->assign('members', $model['members']);
	}
}

?>
