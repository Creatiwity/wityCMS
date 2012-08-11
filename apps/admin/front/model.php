<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/admin/front/model.php 0003 17-09-2011 Fofif $
 */

class AdminModel {
	private $db;
	
	public function __construct() {
		$this->db = WSystem::getDB();
	}
	
	/**
	 * Récupère la liste des applis administrables
	 * @return array
	 */
	public function getAdminAppList() {
		static $adminApps = array();
		if (empty($adminApps)) {
			$apps = glob(APPS_DIR.'*', GLOB_ONLYDIR);
			foreach ($apps as $app) {
				if (file_exists($app.DS.'admin'.DS.'main.php') && AdminController::checkAccess(strtolower(basename($app)))) {
					$adminApps[] = basename($app);
				}
			}
		}
		return $adminApps;
	}
	
	public function loadAppData($app) {
		WConfig::load($app.'_info', APPS_DIR.$app.DS.'admin'.DS.'infos.xml', 'xml');
		return WConfig::get($app.'_info');
	}
	
	public function getLevels($app) {
		$data = $this->loadAppData($app);
		if (!empty($data->acl)) {
			$levels = array();
			for ($i = 0; $i < count($data->acl->level); $i++) {
				if (!empty($data->acl->level[$i]->name)) {
					$levels[] = array(
						'id' => intval($data->acl->level[$i]->id),
						'name' => $data->acl->level[$i]->name
					);
				}
			}
			return $levels;
		} else {
			return array();
		}
	}
}

?>