<?php
/**
 * WDatabase.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WDate manages dates using the user's custom timezone.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class WDate extends DateTime implements JsonSerializable {
	/**
	 * This function handles user's custom timezone.
	 *
	 * @param string $date If empty, use the current date. See DateTime class for full documentation.
	 */
	public function __construct($date = '', $timezone = null) {
		if (empty($timezone)) {
			$timezone = $this->getUserTimezone();
		}

		// Build the date
		parent::__construct($date, $timezone);
	}

	public static function getUserTimezone() {
		// Try user timezone
		if (!empty($_SESSION['timezone'])) {
			$user_timezone = $_SESSION['timezone'];
			try {
				return new DateTimeZone($user_timezone);
			} catch (Exception $e) {}
		}

		// Try config timezone
		$config_timezone = WConfig::get('config.timezone');
		if (!empty($config_timezone)) {
			try {
				return new DateTimeZone($config_timezone);
			} catch (Exception $e) {}
		}

		// User server timezone
		$server_timezone = ini_get('date.timezone');
		if (!empty($server_timezone)) {
			try {
				return new DateTimeZone($server_timezone);
			} catch (Exception $e) {}
		}

		// Default is GMT
		return new DateTimeZone('GMT');
	}

	/**
	 * Translates the object into a string compatible with SQL format: 2013-01-30 13:37:00.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->format('Y-m-d H:i:s');
	}

	/**
	 * Calculates the time elapsed from this date.
	 *
	 * @return string A phrase like this "3 seconds ago"
	 */
	public function getElapsedTime() {
		$diff = $this->diff(new DateTime());

		if ($diff->invert == 1) {
			return $this->format(WLang::_('wdate_format'));
		}

		if ($diff->y > 1) {
			return WLang::_('wdate_years_ago', $diff->y);
		} else if ($diff->y > 1) {
			return WLang::_('wdate_year_ago');
		}

		if ($diff->m > 0) {
			return WLang::_('wdate_months_ago', $diff->m);
		} else if ($diff->m > 1) {
			return WLang::_('wdate_month_ago');
		}

		if ($diff->d >= 14) {
			return WLang::_('wdate_weeks_ago', ceil($diff->d/7));
		} else if ($diff->d >= 7) {
			return WLang::_('wdate_week_ago');
		} else if ($diff->d > 1) {
			return WLang::_('wdate_days_ago', $diff->d);
		} else if ($diff->d == 1) {
			return WLang::_('wdate_day_ago');
		}

		if ($diff->h > 1) {
			return WLang::_('wdate_hours_ago', $diff->h);
		} else if ($diff->h == 1) {
			return WLang::_('wdate_hour_ago', $diff->h);
		}

		if ($diff->i > 1) {
			return WLang::_('wdate_minutes_ago', $diff->i);
		} else if ($diff->i == 1) {
			return WLang::_('wdate_minute_ago');
		}

		if ($diff->s > 1) {
			return WLang::_('wdate_seconds_ago', $diff->s);
		} else if ($diff->s == 1) {
			return WLang::_('wdate_second_ago', $diff->s);
		}

		return WLang::_('now');
	}

	public function jsonSerialize() {
		return $this->format(WDate::ISO8601);
	}
}

?>
