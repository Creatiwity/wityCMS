<?php
/**
 * wityCMS index.php start-up file
 *
 * Content Management System for everyone.
 *
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */

/**
 * wityCMS version number
 */
define('WITYCMS_VERSION', '0.6.2');

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
require_once SYS_DIR.'WTemplate'.DS.'WTemplate.php';
require_once SYS_DIR.'WCore'.DS.'WSystem.php';
require_once SYS_DIR.'WCore'.DS.'WSession.php';
require_once SYS_DIR.'WCore'.DS.'WDatabase.php';
require_once SYS_DIR.'WCore'.DS.'WRoute.php';
require_once SYS_DIR.'WCore'.DS.'WConfig.php';
require_once SYS_DIR.'WCore'.DS.'WRequest.php';
require_once SYS_DIR.'WCore'.DS.'WLang.php';
require_once SYS_DIR.'WCore'.DS.'WDate.php';
require_once SYS_DIR.'WCore'.DS.'WNote.php';
require_once SYS_DIR.'WCore'.DS.'WHelper.php';
require_once SYS_DIR.'WCore'.DS.'WTools.php';
require_once SYS_DIR.'WCore'.DS.'WExport.php';
require_once SYS_DIR.'WCore'.DS.'WRetriever.php';
require_once SYS_DIR.'WCore'.DS.'WResponse.php';
require_once SYS_DIR.'WCore'.DS.'WMain.php';

/**
 * Installer section
 */
if (file_exists(WITY_PATH.'installer/installer.php') && !file_exists(CONFIG_DIR.'config.php')) {
	WRoute::init();

	// Redirect user to root directory if not already on it
	if (WRoute::getQuery() != '') {
		header('Location: '.WRoute::getDir());
		exit();
	}

	require 'installer/installer.php';
	$installer = new Installer();
	$installer->launch();

	exit();
}

/**
 * Execute Wity
 */
$wity = new WMain();

?>
