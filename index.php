<?php
/**
 * WityCMS index.php start-up file
 * 
 * Content Management System for everyone.
 * 
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0
 */

/**
 * WityCMS version number
 */
define('IN_WITY', true);
define('WITYCMS_VERSION', '0.4.0');

/**
 * Error reporting level = MAXIMUM
 */
error_reporting(E_ALL);

/**
 * Files paths
 */
require_once 'paths.php';

/**
 * Core classes inclusion
 */
require_once SYS_DIR.'WCore'.DS.'WSystem.php';
require_once SYS_DIR.'WCore'.DS.'WDatabase.php';
require_once SYS_DIR.'WCore'.DS.'WRoute.php';
require_once SYS_DIR.'WCore'.DS.'WConfig.php';
require_once SYS_DIR.'WCore'.DS.'WRequest.php';
require_once SYS_DIR.'WCore'.DS.'WLang.php';
require_once SYS_DIR.'WCore'.DS.'WDate.php';
require_once SYS_DIR.'WCore'.DS.'WNote.php';
require_once SYS_DIR.'WCore'.DS.'WHelper.php';
require_once SYS_DIR.'WCore'.DS.'WRetriever.php';
require_once SYS_DIR.'WCore'.DS.'WResponse.php';
require_once SYS_DIR.'WCore'.DS.'WMain.php';

/**
 * Installer section
 */
if (file_exists(WITY_PATH.'installer/installer.php') && !file_exists(CONFIG_DIR.'config.php')) {
	WRoute::init();
	if (WRoute::getQuery() != '/') {
		header('Location: '.WRoute::getDir());
	}

	require 'installer/installer.php';
	$installer = new Installer();
	$installer->launch();

	return;
}

/**
 * Execute Wity
 */
$wity = new WMain();

?>
