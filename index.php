<?php
/**
 * WityCMS index.php start-file
 * 
 * Content Management System for everyone
 *
 * @package System
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version	0.3
 */

/**
 * Security system to avoid direct access to the other php files
 */
define('IN_WITY', true);
define('WITY_VERSION', '0.3.0');

/**
 * Installer section
 */
if(file_exists("installer/installer.php")) {
    require 'installer/installer.php';
    Installer::launch();
    return;
}

/**
 * Error reporting level = MAXIMUM
*/
error_reporting(E_ALL);

/**
 * Files paths
 */
require 'paths.php';

/**
 * Core classes inclusion
 */
require SYS_DIR.'WCore'.DS.'WSystem.php';
require SYS_DIR.'WCore'.DS.'WDatabase.php';
require SYS_DIR.'WCore'.DS.'WRoute.php';
require SYS_DIR.'WCore'.DS.'WConfig.php';
require SYS_DIR.'WCore'.DS.'WRequest.php';
require SYS_DIR.'WCore'.DS.'WLang.php';
require SYS_DIR.'WCore'.DS.'WNote.php';
require SYS_DIR.'WCore'.DS.'WHelper.php';
require SYS_DIR.'WCore'.DS.'WMain.php';

/**
 * Execute Wity
 */
$wity = new WMain();

?>