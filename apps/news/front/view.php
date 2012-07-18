<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif
 * @version	$Id: view.php 0001 09-04-2011 Fofif $
 */

class NewsView extends WView {
	private $model;
	
	public function __construct(NewsModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	public function index() {
		$data = $this->model->getNewsList(0, 3);
		foreach ($data as $values) {
			$values['debut'] = array_shift(explode('<hr />', $values['content']));
			$values['content'] = str_replace('<hr />', '', $values['content']);
			$this->tpl->assignBlockVars('news', $values);
		}
	}
	
	public function detail($id) {
		$data = $this->model->loadNews($id);
		$data['content'] = str_replace('<hr />', '', $data['content']);
		$this->assign($data);
	}
}

?>