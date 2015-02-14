<?php
/**
 * Slideshow Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SlideshowAdminView is the Admin View of the Slideshow Application
 * 
 * @package Apps\Slideshow\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 1.0.0-07-02-2015
 */
class SlideshowAdminView extends WView {
	public function __construct() {
		parent::__construct();
		
		$this->assign('css', '/apps/slideshow/admin/css/slideshow-admin.css');
	}
	
	public function slides($model) {
		$this->assign('slides', $model);
		$this->assign('require', "witycms/admin");
	}
	
	private function slide_form($model) {
		$this->assign('js', "/libraries/ckeditor-4.4.5/ckeditor.js");
		$this->assign('require', "witycms/admin");
		
		$default = array(
			'image' => '',
			'url'   => '',
		);
		$default_translatable = array(
			'title'  => '',
			'legend' => '',
		);
		$lang_list = array(1);
		
		foreach ($default_translatable as $key => $value) {
			foreach ($lang_list as $id_lang) {
				$default[$key.'_'.$id_lang] = $value;
			}
		}

		$this->assignDefault($default, $model);
		
		$this->setTemplate('slide_form');
	}
	
	public function slide_add($model) {
		$this->slide_form($model);
	}
	
	public function slide_edit($model) {
		$this->slide_form($model);
	}
	
	public function slide_delete($model) {
		$this->assign('title', $model['title_1']);
		$this->assign('confirm_delete_url', '/admin/slideshow/slide_delete/'.$model['id'].'/confirm');
	}
	
	public function configuration(array $model) {
		$this->assign('config', $model);
	}
}

?>
