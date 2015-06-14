<?php
/**
 * Team Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * TeamController is the Front Controller of the Team Application.
 * 
 * @package Apps\Team\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-dev-07-06-2015
 */
class TeamController extends WController {
	protected function members(array $params) {
		return array(
			'members' => $this->model->getMembers()
		);
	}
}

?>
