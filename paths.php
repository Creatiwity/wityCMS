<?php
/**
 * WityCMS paths.php
 * 
 * Content Management System for everyone
 *
 * @package System
 * @author Charly Poly <charly[dot]poly at live[dot]fr>
 * @version	0.3
 */

/**
 * Avoid direct access
 */
defined('IN_WITY') or die('Access denied');

/**
 * Séparateur de dossiers
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Racine du CMS
 */
define('WITY_PATH', dirname(__FILE__).DS);

/**
 * Emplacement du Système
 */
define('SYS_DIR', WITY_PATH.'system'.DS);

/**
 * Emplacement des Helpers
 */
define('HELPERS_DIR', WITY_PATH.'helpers'.DS);

/**
 * Emplacement des Librairies
 */
define('LIBS_DIR', WITY_PATH.'libraries'.DS);

/**
 * Emplacement des Applications
 */
define('APPS_DIR', WITY_PATH.'apps'.DS);

/**
 * Emplacement des Thèmes
 */
define('THEMES_DIR', WITY_PATH.'themes'.DS);

/**
 * Emplacement des Logs
 */
define('LOGS_DIR', WITY_PATH.'helpers'.DS);

/**
 * Emplacement des Fichiers de cache
 */
define('CACHE_DIR', WITY_PATH.'cache'.DS);



?>