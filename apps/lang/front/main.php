<?php
/**
 * Lang Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * LangController is the Front Controller of the Lang Application
 *
 * @package Apps\Lang\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class LangController extends WController {
	protected function select($params) {
		$lang = array_shift($params);

		switch ($lang) {
			default:
			case 'fr':
				$_SESSION['lang'] = 'fr_FR';
				$_SESSION['lang_iso'] = 'fr';
				break;

			case 'en':
				$_SESSION['lang'] = 'en_EN';
				$_SESSION['lang_iso'] = 'en';
				break;

			case 'es':
				$_SESSION['lang'] = 'es_ES';
				$_SESSION['lang_iso'] = 'es';
				break;
		}

		// Redirection
		$this->setHeader('Location', WRoute::getReferer());
	}
}

?>
