<?php
/**
 * Media Application - Admin Model - /apps/media/admin/model.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

// Include Front Model for inheritance
include_once APPS_DIR.'media'.DS.'front'.DS.'model.php';

/**
 * MediaModel is the Admin Model of the Media Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class MediaAdminModel extends MediaModel {
	public function __construct() {
		parent::__construct();
	}
}
