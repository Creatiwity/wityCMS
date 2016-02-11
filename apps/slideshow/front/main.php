<?php
/**
 * Slideshow Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SlideshowController is the Front Controller of the Slideshow Application
 *
 * @package Apps\Slideshow\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class SlideshowController extends WController {
	protected function block(array $params) {
		return array(
			'slides' => $this->model->getSlides(),
			'config' => $this->model->getConfig()
		);
	}
}

?>
