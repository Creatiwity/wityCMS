<?php
/**
 * Search Application - Front Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * SearchController is the Front Controller of the Search Application
 *
 * @package Apps\Search\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
class SearchController extends WController {
	protected function form(array $params) {
		$query = WRequest::get('query');

		// Array indexed by app
		// $results = array(
		//   'page' => array(
		//     array(
		//       'url' => 'http://',
		//       'excerpt' => 'test'
		//     )
		//   )
		// );
		$results = array();

		// Do researches
		if (!empty($query)) {
			$apps = WRetriever::getAppsList(false);

			foreach ($apps as $app) {
				// Only front app
				$manifest = $this->loadManifest($app);

				if (isset($manifest['actions']['search'])) {
					$search_model = WRetriever::getModel($app.'/search', array($query));

					if (!empty($search_model['result'])) {
						$results[$app] = $search_model['result'];
					}
				}
			}
		}

		return array(
			'query'   => $query,
			'results' => $results
		);
	}

}

?>
