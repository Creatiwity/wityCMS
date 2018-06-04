<?php
/**
 * WTools.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WTools contains some tiny helpful functions.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WTools {
	/**
	 * Removes accents from a string.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function stripAccents($string) {
		return strtr(
			utf8_decode($string),
			utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
			'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
		);
	}

	/**
	 * Verifies whether a string is a valid email.
	 *
	 * @param string $string
	 * @return bool
	 */
	public static function isEmail($string) {
		return (!empty($string) && preg_match('#^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$#i', $string));
	}

	/**
	 * Transforms a string into HTTP request
	 *
	 * @param string $url
	 * @return string starting with http://
	 */
	public static function secureURL($url) {
		if (!empty($url) && strpos($url, 'http') === false) {
			return 'http://'.$url;
		}

		return $url;
	}

	/**
	 * Move elements in array
	 *
	 * @param array $array Array to reorder (reference)
	 * @param int $a From position
	 * @param int $b To new position
	 */
	public static function moveElementInArray(&$array, $a, $b) {
		$out = array_splice($array, $a, 1);
		array_splice($array, $b, 0, $out);
	}

	/**
	 * Generates a random string
	 *
	 * @param int $length
	 * @return string
	 */
	public static function generateString($length = 10) {
		$characters = '01234567890123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen($characters);
		$random_string = '';

		for ($i = 0; $i < $length; $i++) {
		$random_string .= $characters[rand(0, $characters_length - 1)];
		}

		return $random_string;
	}
}

?>
