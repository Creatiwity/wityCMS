<?php 
/**
 * WNote.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WNote manages all notes : stores, displays, ...
 *
 * @package System\WCore
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-22-11-2012
 */
class WNote {
	// Notes levels
	const ERROR   = 'error';
	const INFO    = 'info';
	const SUCCESS = 'success';

	/**
	 * @var array() Notes to be displayed in a fallback view
	 */
	private static $custom_stack = array();
	
	/**
	 * Raise a new note
	 * 
	 * @todo Find an more elegant way than killing in case of error
	 * @param  string $level   note's level
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array(string) the 3 arguments $level, $code and $message in an array()
	 */
	public static function raise($level, $code, $message, $handler) {
		// Note creation
		$note = array(
			'level'   => $level,
			'code'    => $code,
			'message' => $message
		);
		
		$function = 'handle_'.$handler;
		if (is_callable(array('WNote', $function))) {
			// Execute handler
			self::$function($note);
			return $note;
		} else {
			// If no handler was found, don't let the screen blank
			die("WNote::raise() : Unfound handler <strong>\"".$handler."\"</strong><br /><u>Triggering note:</u>\n".self::handle_html($note));
		}
	}
	
	/**
	 * Derived from self::raise() with $level set to ERROR
	 * 
	 * @see WNote::raise()
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array(string) the 3 arguments $level, $code and $message in an array()
	 */
	public static function error($code, $message, $handler = 'assign') {
		return self::raise(self::ERROR, $code, $message, $handler);
	}
	
	/**
	 * Derived from self::raise() with $level set to INFO
	 * 
	 * @see WNote::raise()
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array(string) the 3 arguments $level, $code and $message in an array()
	 */
	public static function info($code, $message, $handler = 'assign') {
		return self::raise(self::INFO, $code, $message, $handler);
	}
	
	/**
	 * Derived from self::raise() with $level set to SUCCESS
	 * 
	 * @see WNote::raise()
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array(string) the 3 arguments $level, $code and $message in an array()
	 */
	public static function success($code, $message, $handler = 'assign') {
		return self::raise(self::SUCCESS, $code, $message, $handler);
	}
	
	/**
	 * Ignore the note
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 */
	public static function handle_ignore($note) {
		// do nothing...
	}
	
	/**
	 * Returns an HTML form of the note
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 * @return string HTML form of the note
	 */
	public static function handle_html($note) {
		return "<ul><li><strong>level:</strong> ".$note['level']."</li>\n"
			."<li><strong>code:</strong> ".$note['code']."</li>\n"
			."<li><strong>message:</strong> ".$note['message']."</li>\n"
			."</ul><br />\n";
	}
	
	/**
	 * Displays the note in an HTML form just before killing the script
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 */
	public static function handle_die($note) {
		die(self::handle_html($note));
	}
	
	/**
	 * Adds a note in the SESSION variable stack in order to display it when rendering the whole page
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 */
	public static function handle_assign($note) {
		if (self::count($note['code']) == 0) {
			$_SESSION['notes'][] = $note;
		}
	}
	
	/**
	 * Renders the note as it was the main application with its own view
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 */
	public static function handle_display($note) {
		// own view
		$view = new WView();
		$view->setTheme(WConfig::get('config.theme'));
		$view->setResponse('themes/system/note/note_view.html');
		$view->assign('css', '/themes/system/note/note.css');
		$view->assign('notes_data', array($note));
		$view->render();
	}
	
	/**
	 * Stores the note in the WNote::$custom_stack in order to display it in a fallback view
	 * 
	 * @param array(string) $note a note as it is returned by WNote::raise()
	 */
	public static function handle_custom($note) {
		self::$custom_stack[] = $note;
	}
	
	/**
	 * Returns and unset from the SESSION stack all notes whose $code is matching the $pattern
	 * 
	 * @param string $pattern optional pattern to find a note by its code
	 * @return array array of all notes whose $code is matching the $pattern
	 */
	public static function get($pattern = "*") {
		$result = array();
		if (!empty($_SESSION['notes'])) {
			foreach ($_SESSION['notes'] as $key => $note) {
				if ($pattern == '*' || $note['code'] == $pattern || (strpos($pattern, '*') !== false && preg_match('#'.str_replace('*', '.*', $pattern).'#', $note['code']))) {
					$result[] = $note;
					// remove the note
					unset($_SESSION['notes'][$key]);
				}
			}
		}
		return $result;
	}
	
	/**
	 * Returns the number of notes in the SESSION stack whose $code is matching the $pattern
	 * @param string $pattern optional pattern to find a note by its code
	 * @return int number of notes whose $code is matching the $pattern
	 */
	public static function count($pattern = "*") {
		$count = 0;
		if (!empty($_SESSION['notes'])) {
			foreach ($_SESSION['notes'] as $key => $note) {
				if ($pattern == '*' || $note['code'] == $pattern || (strpos($pattern, '*') !== false && preg_match('#'.str_replace('*', '.*', $pattern).'#', $note['code']))) {
					$count++;
				}
			}
		}
		return $count;
	}
	
	/**
	 * Parses a set of notes and returns the html response
	 * 
	 * @param array $notes notes that will be parsed
	 * @return string the HTML response
	 */
	public static function parse(array $notes) {
		if (empty($notes)) {
			return "";
		}
		$tpl = WSystem::getTemplate();
		$tpl->assign('css', $tpl->getVar('css').'<link href="/themes/system/note/note.css" rel="stylesheet" type="text/css" media="screen" />'."\n");
		$tpl->assign('notes_data', $notes);
		$html = $tpl->parse('themes/system/note/note_view.html');
		$tpl->clear('notes_data');
		return $html;
	}
	
	/**
	 * Display a set of notes in a fallback view
	 * 
	 * @return boolean true if there were some notes to render, false otherwise
	 */
	public static function displayCustomView() {
		// Generate view
		if (!empty(self::$custom_stack)) {
			$view = new WView();
			$view->setTheme('_blank');
			$view->setResponse('themes/system/note/note_full_view.html');
			$view->assign('notes_data', self::$custom_stack);
			if (!$view->render()) {
				self::error("note_display_custom_view", "WView did not manage to display the custom error page (themes/system/note/note_full_view.html).<br />\n"
					."<u>Triggering notes:</u>\n"
					.implode('', array_map('WNote::handle_html', self::$custom_stack)), 'die');
			}
			return true;
		}
		return false;
	}
}

?>