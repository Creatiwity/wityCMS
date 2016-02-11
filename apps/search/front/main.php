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
 * @version 0.5.0-11-02-2016
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
			$apps = WRetriever::getAppsList();

			foreach ($apps as $app) {
				// Only front app
				if (strpos($app, 'admin/') === false) {
					$manifest = $this->loadManifest($app);

					if (isset($manifest['actions']['search'])) {
						$search_model = WRetriever::getModel($app, array('search', $query));

						if (!empty($search_model['result'])) {
							$results[$app] = $search_model['result'];
						}
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
