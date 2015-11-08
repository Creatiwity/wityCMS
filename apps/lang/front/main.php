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
 * @version 0.5.0-dev-14-06-2015
 */
class LangController extends WController {
	protected function select($params) {
		$lang = array_shift($params);

		switch ($lang) {
			default:
			case 'fr':
				$_SESSION['lang'] = 'fr';
				break;

			case 'en':
				$_SESSION['lang'] = 'en';
				break;

			case 'es':
				$_SESSION['lang'] = 'es';
				break;
		}

		// Redirection
		$this->setHeader('Location', WRoute::getReferer());
	}
}

?>
