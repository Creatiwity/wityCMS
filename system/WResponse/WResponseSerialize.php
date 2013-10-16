<?php 
/**
 * WResponseSerialize.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WResponseSerialize is a plugin for WResponse.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-16-10-2013
 */
class WResponseSerialize implements WResponseMode {
	private $mode;
	private $model;
	
	public function __construct($mode) {
		$this->mode = $mode;
	}
	
	public function setModel($model) {
		$this->model = $model;
	}
	
	public function renderHeaders() {
		return false;
	}
	
	/**
	 * Final render of the response
	 * Displays a valid HTML5 to the screen
	 * 
	 * @param WView $view The view to render as a main instance
	 */
	public function render($notes) {
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			
		}
		
		// Store the notes
		$this->model['notes'] = $notes;
		
		// Depending on the mode, add the view and/or remove the application's result
		if ($this->mode == 'v') {
			unset($this->model['result']);
			
			$this->model['view'] = WRetriever::getViewFromModel($this->model)->render();
		} else if ($this->mode == 'mv') {
			$this->model['view'] = WRetriever::getViewFromModel($this->model)->render();
		}
		
		return json_encode($this->model, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);;
	}
}

?>
