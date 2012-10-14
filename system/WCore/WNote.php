<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WNote.php 0006 19-09-2012 Fofif $
 * @package Wity
 */

class WNote {
	// Notes levels
	const ERROR   = 'error';
	const INFO    = 'info';
	const SUCCESS = 'success';
	
	/**
	 * Crée une nouvelle note
	 * 
	 * @static
	 * @param  string $level   Niveau de la note
	 * @param  string $code    Intitulé de la note
	 * @param  string $message Message de la note
	 * @return $note
	 */
	public static function raise($level, $code, $message, $handler) {
		// Création d'une nouvelle note
		$note = array(
			'level'   => $level,
			'code'    => $code,
			'message' => nl2br($message)
		);
		
		$function = 'handle_'.$handler;
		if (is_callable(array('WNote', $function))) {
			// Execution du handler
			self::$function($note);
			return $note;
		} else {
			// On évite de laisser l'écran vide
			die("WNote::raise() : Unfound handler <strong>\"".$handler."\"</strong><br />Triggering note : Level: ".$level."<br />Code: ".$code."<br />Message: ".$message);
		}
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function error($code, $message, $handler = 'assign') {
		//var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		return self::raise(self::ERROR, $code, $message, $handler);
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function info($code, $message, $handler = 'assign') {
		return self::raise(self::INFO, $code, $message, $handler);
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function success($code, $message, $handler = 'assign') {
		return self::raise(self::SUCCESS, $code, $message, $handler);
	}
	
	public static function handle_ignore($note) {
		// do nothing...
	}
	
	/**
	 * Display the note dying the whole execution
	 * 
	 * @param array $note
	 */
	public static function handle_die($note) {
		die("<p>".strtoupper($note['level'])." - <strong>".$note['code'].":</strong> ".$note['message']."</p>\n");
	}
	
	/**
	 * Assign a note in the stack
	 * 
	 * @param array $note
	 */
	public static function handle_assign($note) {
		if (self::count($note['code']) == 0) {
			$_SESSION['notes'][] = $note;
		}
	}
	
	/**
	 * Display a note as a response
	 * The aim of this handler is to redirect the response to a note
	 * (not only displaying it as a note in the template)
	 * 
	 * @param array $note
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
	 * Get notes saved into session whose code property matches $code
	 * Notice: once you get a note, you won't get it anymore afterwards
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
	 * Counts the notes saved whose code property matches $pattern
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
	 * Display a set of notes in a dedicated view
	 */
	public static function displayFull(array $notes) {
		static $mutex = false;
		if ($mutex) {
			self::error('wnote_critical_section', "WNote::diplay_full(): tried to enter into a critical section. Probably nesting.\n
			Triggering notes :\n".serialize($notes), 'die');
		}
		$mutex = true;
		
		// If no notes found, display a custom message
		if (empty($notes)) {
			$notes = array(self::info("There is no note to display.", '', 'ignore'));
		}
		
		// Generate view
		$view = new WView();
		$view->setTheme('_blank');
		$view->setResponse('themes/system/note/note_full_view.html');
		$view->assign('notes', $notes);
		$view->render();
		
		$mutex = false;
	}
}


?>