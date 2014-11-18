<?php 
/**
 * WTools.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WTools contains some tiny helpful functions.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.5.0-dev-09-01-2013
 */
class WTools {
	public static function stripAccents($string) {
		return strtr(
			utf8_decode($string), 
			utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 
			'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
		);
	}
}

?>
