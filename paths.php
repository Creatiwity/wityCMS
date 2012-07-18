<?php defined('IN_WITY') or die('Access denied');
/*
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Défintion de la structure de wity qui est modulable
 * 
 * @author Charly Poly <charly[dot]poly at live[dot]fr>
 */

/**
 * Séparateur de dossiers
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Racine du CMS
 */
define('WITY_PATH', dirname(__FILE__).DS);

/**
 * Emplacement du Système
 */
define('SYS_DIR', WITY_PATH.'system'.DS);

/**
 * Emplacement des Helpers
 */
define('HELPERS_DIR', WITY_PATH.'helpers'.DS);

/**
 * Emplacement des Librairies
 */
define('LIBS_DIR', WITY_PATH.'libraries'.DS);

/**
 * Emplacement des Applications
 */
define('APPS_DIR', WITY_PATH.'apps'.DS);

/**
 * Emplacement des Thèmes
 */
define('THEMES_DIR', WITY_PATH.'themes'.DS);

/**
 * Emplacement des Logs
 */
define('LOGS_DIR', WITY_PATH.'helpers'.DS);

/**
 * Emplacement des Fichiers de cache
 */
define('CACHE_DIR', WITY_PATH.'cache'.DS);



?>