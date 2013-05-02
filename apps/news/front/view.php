<?php
/**
 * News Application - Front View - /apps/news/front/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsView is the Front View of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class NewsView extends WView {
	private $model;
	
	public function __construct(NewsModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	public function listing() {
		$this->assign('news', $this->model->getNewsList(0, 3));
		$this->render('listing');
	}
	
	public function detail($news_id) {
		$data = $this->model->getNews($news_id);
		$data['news_content'] = str_replace('<hr />', '', $data['news_content']);
		$this->assign($data);
		$this->render('details');
	}
}

?>