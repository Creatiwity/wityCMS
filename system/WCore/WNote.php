<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author  Fofif
 * @version	$Id: WCore/WNote.php 0004 21-07-2012 Fofif $
 * @desc	Affichage de messages
 */

class WNote {
	// Les niveaux de note
	const SYSTEM  = 'system';
	const ERROR   = 'error';
	const INFO    = 'info';
	const SUCCESS = 'success';
	
	/**
	 * Crée une nouvelle note
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
			'message' => $message
		);
		
		$func = 'handle_'.$handler;
		if (is_callable(array('WNote', $func))) {
			// Execution du handler
			self::$func($note);
			return $note;
		} else {
			// On évite de laisser l'écran vide
			die("WNote::raise() : Unfound handler <strong>\"".$handler."\"</strong><br />Triggering note : ".$message);
		}
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function system($code, $message, $handler = 'die') {
		$note = self::raise(self::ERROR, $code, $message, $handler);
		return $note;
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function error($code, $message, $handler = 'session') {
		$note = self::raise(self::ERROR, $code, $message, $handler);
		return $note;
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function info($code, $message, $handler = 'session') {
		$note = self::raise(self::INFO, $code, $message, $handler);
		return $note;
	}
	
	/**
	 * Dérivée de self::raise : passe un niveau précis en argument
	 */
	public static function success($code, $message, $handler = 'session') {
		$note = self::raise(self::SUCCESS, $code, $message, $handler);
		return $note;
	}
	
	public static function handle_ignore($note) {
		// do nothing...
	}
	
	/**
	 * Affichage de la note par un die
	 * @param array $note
	 */
	public static function handle_die($note) {
		die("<br /><strong>".$note['code'].":</strong> ".$note['message']."<br />\n");
	}
	
	/**
	 * Assignation de la note parsée dans le tpl
	 * @param array $note
	 */
	public static function handle_assign($note) {
		$tpl = WSystem::getTemplate();
		$tpl->assign(array(
			'note_level'   => $note['level'],
			'note_code'    => $note['code'],
			'note_message' => $note['message'],
			'css'          => $tpl->getVar('css').'<link href="/themes/system/styles/note.css" rel="stylesheet" type="text/css" media="screen" />'."\n"
		));
		$html = $tpl->parse('themes/system/templates/note.html');
		$tpl->clear(array('note_level', 'note_code', 'note_message'));
		// Assign html result as a string in the view
		$tpl->assign('note', $tpl->getVar('note').$html);
	}
	
	/**
	 * Assignation de la note parsée dans le tpl en tant que variable block
	 * @param array $note
	 */
	public static function handle_assign_block($note) {
		$tpl = WSystem::getTemplate();
		$tpl->assignBlockVars('note', array(
			'level'   => $note['level'],
			'code'    => $note['code'],
			'message' => $note['message'],
		));
	}
	
	/**
	 * Récupère les erreurs stockées dans le moteur de template
	 * 
	 * @return array voir handle_assign_block
	 */
	public static function get_assigned_errors() {
		$tpl = WSystem::getTemplate();
		return $tpl->getVar('errors_block');
	}
	
	/**
	 * Affichage d'une page avec la note en tant que réponse HTML
	 * @param array $note
	 */
	public static function handle_display($note) {
		$view = new WView();
		$view->assign(array(
			'note_level'   => $note['level'],
			'note_code'    => $note['code'],
			'note_message' => $note['message'],
			'css'          => '/themes/system/styles/note.css'
		));
		try {
			$view->setResponse('themes/system/templates/note.html');
			$view->render();
		} catch (Exception $e) {
			self::raise(
				self::ERROR, 
				"WView error", 
				nl2br(sprintf("%s\n\nTriggering note :\nLevel: %s\nCode: %s\nMessage: %s", $e->getMessage(), $note['level'], $note['code'], $note['message'])),
				'display_custom'
			);
		}
	}
	
	/**
	 * Affichage d'une page personnalisée pour la note
	 * Le thème est désactivé par ce handler
	 * @param array $note
	 */
	public static function handle_display_custom($note) {
		$view = new WView();
		$view->setTheme('_blank');
		$view->assign(array(
			'note_level'   => $note['level'],
			'note_code'    => $note['code'],
			'note_message' => $note['message'],
			'css'          => '/themes/system/styles/note.css'
		));
		$view->setResponse('themes/system/templates/note_display_custom.html');
		$view->render();
	}
	
	/**
	 * Assignation de la note parsée en session
	 * @param array $note
	 */
	public static function handle_session($note) {
		if (!isset($_SESSION['note_queue'])) {
			$_SESSION['note_queue'] = array($note);
		} else {
			array_push($_SESSION['note_queue'], $note);
		}
	}
	
	/**
	 * Traite la file d'attente des notes stockées en session
	 * 
	 * @param string $def_handler Handler par défaut à utiliser lors du traitement
	 */
	public static function treatNoteSession($def_handler = 'assign') {
		if (!empty($_SESSION['note_queue'])) {
			foreach ($_SESSION['note_queue'] as $note) {
				self::raise($note['level'], $note['code'], $note['message'], $def_handler);
			}
			
			// Nettoyage de la pile
			self::cleanSession();
		}
	}
	
	/**
	 * Fonction de nettoyage de la file d'attente
	 */
	public static function cleanSession() {
		unset($_SESSION['note_queue']);
	}
}


?>