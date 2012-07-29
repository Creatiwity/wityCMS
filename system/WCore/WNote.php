<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WNote.php 0005 27-07-2012 Fofif $
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
		$note = self::raise(self::ERROR, $code, $message, $handler);
		return $note;
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function info($code, $message, $handler = 'assign') {
		$note = self::raise(self::INFO, $code, $message, $handler);
		return $note;
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function success($code, $message, $handler = 'assign') {
		$note = self::raise(self::SUCCESS, $code, $message, $handler);
		return $note;
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
		die("<br /><strong>".$note['code'].":</strong> ".$note['message']."<br />\n");
	}
	
	/**
	 * Assignation de la note parsée dans le tpl
	 * 
	 * @param array $note
	 */
	public static function handle_assign($note) {
		$_SESSION['notes'][] = $note;
	}
	
	/**
	 * Affichage d'une page avec la note en tant que réponse HTML
	 * 
	 * @param array $note
	 */
	public static function handle_display($note) {
		// own view
		$view = new WView();
		$view->assign(array(
			'note_level'   => $note['level'],
			'note_code'    => $note['code'],
			'note_message' => $note['message'],
			'css'          => '/themes/system/note/note.css'
		));
		$view->setTheme(WConfig::get('config.theme'));
		$view->setResponse('themes/system/note/note_view.html');
		$view->render();
	}
	
	/**
	 * Get notes saved into session whose code property matches $code
	 */
	public static function get($code = '*') {
		$result = array();
		if (!empty($_SESSION['notes'])) {
			foreach ($_SESSION['notes'] as $key => $note) {
				if ($code == '*' || $note['code'] == $code || (strpos($code, '*') !== false && preg_match('#'.str_replace('*', '.*', $code).'#', $note['code']))) {
					$result[] = $note;
					// remove the note
					unset($_SESSION['notes'][$key]);
				}
			}
		}
		return $result;
	}
	
	/**
	 * Counts the notes saved whose code property matches $code
	 */
	public static function count($code = '*') {
		$count = 0;
		if (!empty($_SESSION['notes'])) {
			foreach ($_SESSION['notes'] as $key => $note) {
				if ($code == '*' || $note['code'] == $code || (strpos($code, '*') !== false && preg_match('#'.str_replace('*', '.*', $code).'#', $note['code']))) {
					$count++;
				}
			}
		}
		return $count;
	}
}


?>