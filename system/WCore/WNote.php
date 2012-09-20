<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WNote.php 0005 27-07-2012 Fofif $
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
	 * Affichage de la note par un die
	 * 
	 * @param array $note
	 */
	public static function handle_die($note) {
		die("<p><strong>".$note['code'].":</strong> ".$note['message']."</p>\n");
	}
	
	/**
	 * Assignation de la note parsée dans le tpl
	 * 
	 * @param array $note
	 */
	public static function handle_assign($note) {
		if (self::count($note['code']) == 0) {
			$_SESSION['notes'][] = $note;
		}
	}
	
	/**
	 * Affichage d'une page avec la note en tant que réponse HTML
	 * 
	 * @param array $note
	 */
	public static function handle_display($note) {
		// own view
		$view = new WView();
		$view->setTheme(WConfig::get('config.theme'));
		$view->setResponse('themes/system/note/note_view.html');
		$view->assign(array(
			'note_level'   => $note['level'],
			'note_code'    => $note['code'],
			'note_message' => $note['message'],
			'css'          => '/themes/system/note/note.css'
		));
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
	
	/**
	 * Display a set of notes in a dedicated view
	 */
	public static function display_full(array $notes) {
		static $mutex = false;
		if ($mutex) {
			self::error('wnote_critical_section', "WNote::diplay_full(): tried to enter into a critical section. Probably nesting.", 'die');
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