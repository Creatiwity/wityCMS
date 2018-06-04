<?php
/**
 * wityCMS paths.php
 *
 * Content Management System for everyone.
 *
 * @version 0.6.2-04-06-2018
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Directory Separator
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * wityCMS Root directory
 */
define('WITY_PATH', dirname(__FILE__).DS);

/**
 * System location
 */
define('SYS_DIR', WITY_PATH.'system'.DS);

/**
 * Configs location
 */
define('CONFIG_DIR', WITY_PATH.'system'.DS.'config'.DS);

/**
 * Helpers location
 */
define('HELPERS_DIR', WITY_PATH.'helpers'.DS);

/**
 * Libraries location
 */
define('LIBS_DIR', WITY_PATH.'libraries'.DS);

/**
 * Applications location
 */
define('APPS_DIR', WITY_PATH.'apps'.DS);

/**
 * Themes location
 */
define('THEMES_DIR', WITY_PATH.'themes'.DS);

/**
 * Logs location
 */
define('LOGS_DIR', SYS_DIR);

/**
 * Cache directory location
 */
define('CACHE_DIR', WITY_PATH.'cache'.DS);

/**
 * Upload directory location
 */
define('UPLOAD_DIR', WITY_PATH.'upload'.DS);

?>
