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
 * @version 0.3-01-03-2013
 */
class WNote {
	/**
	 * Note levels
	 */
	const ERROR   = 'error';
	const INFO    = 'info';
	const SUCCESS = 'success';
	
	/**
	 * @var array Notes to be displayed in a plain view
	 */
	private static $plain_stack = array();
	
	/**
	 * Raise a new note
	 * 
	 * @param  string $level   note's level
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array the 3 arguments $level, $code and $message in an array()
	 */
	public static function raise($level, $code, $message, $handlers) {
		// Note creation
		$note = array(
			'level'   => $level,
			'code'    => $code,
			'message' => $message
		);
		
		$handlers = explode(',', $handlers);
		foreach ($handlers as $handler) {
			$handler = trim($handler);
			$function = 'handle_'.$handler;
			if (is_callable(array('WNote', $function))) {
				// Execute handler
				self::$function($note);
			} else {
				// If no handler was found, don't leave the screen blank
				$note = array(
					'level'   => self::ERROR,
					'code'    => 'note_handler_not_found',
					'message' => "WNote::raise() : Unfound handler <strong>\"".$handler."\"</strong><br /><u>Triggering note:</u>\n".self::handle_html($note)
				);
				self::handle_plain($note);
			}
		}
		return $note;
	}
	
	/**
	 * Derived from self::raise() with $level set to ERROR
	 * 
	 * @see WNote::raise()
	 * @param  string $code    note's code
	 * @param  string $message note's message
	 * @param  string $handler handler to use
	 * @return array the 3 arguments $level, $code and $message in an array()
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
	 * @return array the 3 arguments $level, $code and $message in an array()
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
	 * @return array the 3 arguments $level, $code and $message in an array()
	 */
	public static function success($code, $message, $handler = 'assign') {
		return self::raise(self::SUCCESS, $code, $message, $handler);
	}
	
	/**
	 * Ignore the note
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_ignore($note) {
		// do nothing...
	}
	
	/**
	 * Returns an HTML form of the note
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
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
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_die($note) {
		static $died = false;
		if (!$died) {
			$died = true;
			self::handle_plain($note);
			self::displayPlainView();
			die;
		}
	}
	
	/**
	 * Adds a note in the SESSION variable stack in order to display it when rendering the whole page
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 * @see WNote::count()
	 * @see WNote::get()
	 */
	public static function handle_assign($note) {
		if (self::count($note['code']) == 0) {
			$_SESSION['notes'][] = $note;
		}
	}
	
	/**
	 * Renders the note as the main application
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
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
	 * Handles note to be displayed in a plain HTML View.
	 * Oftenly used for failover purposes (a view did not manage to render since theme cannot be found for instance).
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_plain($note) {
		self::$plain_stack[] = $note;
	}
	
	/**
	 * Log handler
	 * Stores the note in a log file (system/wity.log)
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_log($note) {
		$file = fopen(SYS_DIR.'wity.log', 'a+');
		$text = sprintf("[%s] [%s] [user %s|%s] [route %s] %s - %s\r\n", 
			date('d/m/Y H:i:s', time()), 
			$note['level'], 
			@$_SESSION['userid'], 
			$_SERVER['REMOTE_ADDR'], 
			$_SERVER['REQUEST_URI'], 
			$note['code'], 
			$note['message']
		);
		fwrite($file, $text);
		fclose($file);
	}
	
	/**
	 * Email handler
	 * Sends the note by email to the administrator (defined in config/config.php)
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_email($note) {
		$email = WConfig::get('config.email');
		if (!empty($email)) {
			$mail = WHelper::load('phpmailer');
			$mail->CharSet = 'utf-8';
			$mail->From = $email;
			$mail->FromName = WConfig::get('config.site_name');
			$mail->AddAddress($email);
			$mail->Subject = "[".WConfig::get('config.site_name')."] ".$note['level']." note - ".$note['code'];
			$mail->Body = 
"<p>Dear developper,</p>
<p>A new <strong>".$note['level']."</strong> note was triggered:</p>
<ul>
	<li>Userid: ".@$_SESSION['userid']."</li>
	<li>Client ip: ".$_SERVER['REMOTE_ADDR']."</li>
	<li>Route: ".$_SERVER['REQUEST_URI']."</li>
	<li><strong>Code:</strong> ".$note['code']."</li>
	<li><strong>Message:</strong> ".$note['message']."</li>
</ul>
<p><em>WityNote</em></p>";
			$mail->IsHTML(true);
			$mail->Send();
			unset($mail);
		}
	}
	
	/**
	 * Debug handler
	 * If the debug mode is activated, email and log handlers will be trigered.
	 * 
	 * @param array $note a note as it is returned by WNote::raise()
	 */
	public static function handle_debug($note) {
		if (WConfig::get('config.debug') === true) {
			self::handle_log($note);
			self::handle_email($note);
		}
	}
	
	/**
	 * Returns the number of notes in the SESSION stack whose $code is matching the $pattern
	 * 
	 * @param string $pattern optional pattern to find a note by its code
	 * @return int number of notes whose $code is matching the $pattern
	 */
	public static function count($pattern = '*') {
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
	 * Returns and unset from the SESSION stack all notes whose $code is matching the $pattern
	 * 
	 * @param string $pattern optional pattern to find a note by its code
	 * @return array All notes having its $code matching the $pattern
	 */
	public static function get($pattern = '*') {
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
	 * Parses a set of notes and returns the html response
	 * 
	 * @param array $notes notes that will be parsed
	 * @return string the HTML response
	 */
	public static function parse(array $notes) {
		if (empty($notes)) {
			return '';
		}
		$tpl = WSystem::getTemplate();
		$tpl->assign('css', $tpl->getVar('css').'<link href="/themes/system/note/note.css" rel="stylesheet" type="text/css" media="screen" />'."\n");
		$previous_notes_data = $tpl->getVar('notes_data');
		$tpl->assign('notes_data', $notes);
		$html = $tpl->parse('themes/system/note/note_view.html');
		$tpl->assign('notes_dta', $previous_notes_data);
		return $html;
	}
	
	/**
	 * Display a set of notes in a fallback view
	 * 
	 * @return boolean true if there were some notes to render, false otherwise
	 */
	public static function displayPlainView() {
		// Generate view
		if (!empty(self::$plain_stack)) {
			$notes_data = self::$plain_stack;
			self::$plain_stack = array();
			$view = new WView();
			$view->setTheme('_blank');
			$view->setResponse('themes/system/note/note_plain_view.html');
			$view->assign('css', '/themes/system/note/note.css');
			$view->assign('css', '/themes/system/note/note_plain.css');
			$view->assign('js', '/themes/system/js/jquery-1.8.1.min.js');
			$view->assign('js', '/themes/system/note/note.js');
			$view->assign('notes_data', $notes_data);
			if (!$view->render()) {
				die(
					"WView did not manage to display the Note's Plain View (themes/system/note/note_plain_view.html).<br />\n"
					."<u>Triggering notes:</u>\n"
					.implode('', array_map('WNote::handle_html', $notes_data))
				);
			}
			return true;
		}
		return false;
	}
}

?>