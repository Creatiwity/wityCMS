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
 * @version 0.6.2-04-06-2018
 */
class TeamController extends WController {
	protected function members(array $params) {
		return array(
			'members' => $this->model->getMembers()
		);
	}
}

?>
