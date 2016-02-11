<?php
/**
 * WResponse.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WResponse compiles the final render of wityCMS that will be sent to the browser.
 *
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.5.0-11-02-2016
 */
class WResponse {
	/**
	 * @var string Name of the theme used for the response
	 */
	private $theme_name;

	/**
	 * @var string Directory of the theme used for the response
	 */
	private $theme_dir;

	/**
	 * Assigns a theme
	 *
	 * @param string $theme Theme name (must a be an existing directory in /themes/)
	 */
	public function setTheme($theme) {
		if ($theme == '_blank') {
			$this->theme_name = '_blank';
			$this->theme_dir = 'themes/system/';
		} else if (is_dir(THEMES_DIR.$theme)) {
			$this->theme_name = $theme;
			$this->theme_dir = str_replace(WITY_PATH, '', THEMES_DIR).$theme.DS;
		} else {
			WNote::error('theme_not_found', WLang::get('error_theme_not_found', $theme), 'plain');
			return false;
		}

		return true;
	}

	/**
	 * Returns current theme name
	 *
	 * @return string Current theme name
	 */
	public function getTheme() {
		return $this->theme_name;
	}

	/**
	 * wityCMS's classic render with HTML theme.
	 *
	 * @param WView  $view  View to be rendered
	 * @param string $theme Theme name to use to wrap the view
	 */
	public function render(WView $view = null, $default_theme, $model = array()) {
		// Check headers
		if (isset($model['headers'])) {
			foreach ($model['headers'] as $name => $value) {
				header($name.': '.$value);
			}

			if (isset($model['headers']['location'])) {
				if (is_array($model['result']) && array_keys($model['result']) == array('level', 'code', 'message', 'handlers')) {
					WNote::raise($model['result']);
				}

				return true;
			}
		}

		// Load WTemplate
		$tpl = WSystem::getTemplate();
		if (is_null($tpl)) {
			throw new Exception("WResponse::render(): WTemplate cannot be loaded.");
		}

		// Load in priority theme asked by the view
		$view_theme = $view->getTheme();
		if (empty($view_theme) || !$this->setTheme($view_theme)) {
			$this->setTheme($default_theme);
		}

		// Check theme
		if (empty($this->theme_name)) {
			WNote::error('theme_empty', WLang::get('error_theme_empty'), 'plain');
		}

		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
			$this->setTheme('_blank');
		}

		// Select Theme main template
		if ($this->theme_name == '_blank') {
			$themeMainFile = $this->theme_dir.'_blank.html';
		} else {
			$themeMainFile = $this->theme_dir.'templates'.DS.'index.html';
		}

		try {
			// Define {$include} tpl's var
			$tpl->assign('include', $view->render());

			// Handle notes
			$tpl->assign('notes', WNote::getView(WNote::get('*'))->render());

			$html = $tpl->parse($themeMainFile);

			// Render require configuration
			$require = $tpl->parseString($tpl->getVar('require'));

			// Insert CSS + script configuration
			$css = $tpl->getVar('css');
			$script = $tpl->getVar('js');
			$html = str_replace('</head>', $css.$script."\n".'</head>', $html);

			// Insert requireJS part
			$html = $this->insertRequireJS($html, $require);

			// Absolute links fix
			$html = $this->absoluteLinkFix($html);

			echo $html;
		} catch (Exception $e) {
			WNote::error('final_render_failed', WLang::get('error_final_render_failed', $e->getMessage()), 'die');
			return false;
		}

		return true;
	}

	/**
	 * Fixes absolute links.
	 * If WRoute::getDir() is not the root file, then change links.
	 *
	 * @param string $string A string containing root HTML links
	 * @return string
	 */
	public static function absoluteLinkFix($string) {
		$dir = WRoute::getDir();
		if (!empty($dir)) {
			$string = str_replace(
				array('src="/', 'href="/', 'action="/', 'data-link-modal="/', 'poster="/'),
				array('src="'.$dir, 'href="'.$dir, 'action="'.$dir, 'data-link-modal="'.$dir, 'poster="'.$dir),
				$string
			);
		}

		return $string;
	}

	/**
	 * Inserts requireJS just before closing <body>
	 *
	 * @param string $string The whole compiled page
	 * @param string $string The requirejs part
	 * @return string
	 */
	public static function insertRequireJS($page, $requirejs) {
		return str_replace('</body>', $requirejs."\n".'</body>', $page);
	}

	/**
	 * Renders a model into a JSON view.
	 *
	 * @param array $model Main application's model to display
	 */
	public function renderModel(array $model) {
		// Store the plain notes
		$plain_notes = WNote::getPlain();
		if (!empty($plain_notes)) {
			$model['result'] = $plain_notes;
		}

		// Store the notes
		$model['notes'] = WNote::get('*');

		echo str_replace('\\/', '/', json_encode($model));

		return true;
	}

	/**
	 * Renders the main application's view into a JSON structure, without the application's result.
	 *
	 * @param array $model
	 * @param WView $view
	 */
	public function renderView(array $model, WView $view) {
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
		}

		// Store the notes
		$model['notes'] = WNote::get('*');

		// Remove the application's result
		unset($model['result']);

		$model['view'] = $view->render();

		// Absolute link fix
		$model['view'] = $this->absoluteLinkFix($model['view']);

		echo str_replace('\\/', '/', json_encode($model));

		return true;
	}

	/**
	 * Renders the main application's model + view into a JSON structure.
	 *
	 * @param array $model
	 * @param WView $view
	 */
	public function renderModelView(array $model, WView $view) {
		// Flush the notes waiting for their own view
		$plain_view = WNote::getPlainView();
		if (!is_null($plain_view)) {
			unset($view);
			$view = $plain_view;
		}

		// Store the notes
		$model['notes'] = WNote::get('*');

		$model['view'] = $view->render();

		// Absolute link fix
		$model['view'] = $this->absoluteLinkFix($model['view']);

		echo str_replace('\\/', '/', json_encode($model));

		return true;
	}
}

?>
