<?php
/**
 * WityCMS index.php start-file
 * 
 * Content Management System for everyone
 *
 * @package System
 * @author Johan Dufau <johandufau@gmail.com>
 * @version	0.3
 */

/**
 * Security system to avoid direct access to the other php files
 */
define('IN_WITY', true);
define('WITY_VERSION', '0.2.0');

// Niveau d'affichage des erreurs = maximal
error_reporting(E_ALL);

// Les chemins
require 'paths.php';

// Inclusion des classes principales
require SYS_DIR.'WCore'.DS.'WSystem.php';
require SYS_DIR.'WCore'.DS.'WDatabase.php';
require SYS_DIR.'WCore'.DS.'WRoute.php';
require SYS_DIR.'WCore'.DS.'WConfig.php';
require SYS_DIR.'WCore'.DS.'WRequest.php';
require SYS_DIR.'WCore'.DS.'WLang.php';
require SYS_DIR.'WCore'.DS.'WNote.php';
require SYS_DIR.'WCore'.DS.'WMain.php';

// Execution du main script
$wity = new WMain();

?>