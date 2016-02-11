<?php
/**
 * Sample configuration file for system/config/route.php
 *
 * This file defines the wityCMS's routes configuration.
 * Can be defined the front application to execute by default,
 * and the admin application to execute by default.
 *
 * Also, some "custom routes" can be setup here.
 * According to the pattern provided in the key of $route['custom'],
 * the system will execute the application defined in the corresponding value in the array.
 * See system\WCore\WRoute class for more details.
 */

$route = array(
  'default_front' => 'news',
  'default_admin' => 'admin/news',
  'custom'        => array()
);

?>
