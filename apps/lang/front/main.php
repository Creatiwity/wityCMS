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
 * @version 0.6.2-04-06-2018
 */
class LangController extends WController {
	protected function select($params) {
		$lang = array_shift($params);

		switch ($lang) {
			default:
			case 'fr':
				$_SESSION['current_lang_code'] = 'fr_FR';
				$_SESSION['current_lang_iso'] = 'fr';
				break;

			case 'en':
				$_SESSION['current_lang_code'] = 'en_EN';
				$_SESSION['current_lang_iso'] = 'en';
				break;

			case 'es':
				$_SESSION['current_lang_code'] = 'es_ES';
				$_SESSION['current_lang_iso'] = 'es';
				break;
		}

		// Redirection
		$this->setHeader('Location', WRoute::getReferer());
	}
}

?>
