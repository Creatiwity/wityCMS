<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author    Fofif
 * @version   $Id: WCore/WRequest.php 0003 29-12-2011 Fofif $
 * @desc      Gestion des variables de type REQUEST
 */

class WRequest {
	// On enregistre les variables qui ont été vérifiées pour ne pas refaire la même chose...
	private static $checked = array();
	
	/**
	 * Récupération d'une ou plusieurs valeurs REQUEST
	 * Permet l'utilisation de la syntaxe : list($v1, ...) = WRequest::get(array('v1', 'v2'));
	 * 
	 * @param string|array $names    Noms des valeurs souhaitées
	 * @param mixed        $default  Facultatif: valeurs par défaut
	 * @param string       $hash     Nom de la variable dans laquelle sont stockées les valeurs
	 * @return mixed Tableau de valeurs ou valeur demandée
	 */
	public static function get($names, $default = null, $hash = 'REQUEST') {
		// Data hash
		switch (strtoupper($hash)) {
			case 'GET':
				$data = &$_GET;
				break;
			case 'POST':
				$data = &$_POST;
				break;
			case 'FILES':
				$data = &$_FILES;
				break;
			case 'COOKIE':
				$data = &$_COOKIE;
				break;
			default:
				$data = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		
		if (is_array($names)) {
			// On va parcourir la liste demandée et renvoyer un tableau
			$result = array();
			foreach ($names as $name) {
				$value = self::getValue($data, $name, isset($default[$name]) ? $default[$name] : null, $hash);
				$result[] = $value;
				$result[$name] = $value;
			}
			return $result;
		} else {
			return self::getValue($data, $names, $default, $hash);
		}
	}
	
	/**
	 * Récupération d'une ou plusieurs valeurs REQUEST
	 * Ce mode renvoie un tableau 
	 * 
	 * @param array   $names    Noms des valeurs souhaitées
	 * @param mixed   $default  Facultatif: valeurs par défaut
	 * @param string  $hash     Nom de la variable dans laquelle sont stockées les valeurs
	 * @return array Tableau de valeurs demandées qui ne contient que des clés associées
	 */
	public static function getAssoc(array $names, $default = null, $hash = 'REQUEST') {
		// Data hash
		switch (strtoupper($hash)) {
			case 'GET':
				$data = &$_GET;
				break;
			case 'POST':
				$data = &$_POST;
				break;
			case 'FILES':
				$data = &$_FILES;
				break;
			case 'COOKIE':
				$data = &$_COOKIE;
				break;
			default:
				$data = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		
		// On va parcourir la liste demandée et renvoyer un tableau
		$result = array();
		foreach ($names as $name) {
			$value = self::getValue($data, $name, isset($default[$name]) ? $default[$name] : null, $hash);
			$result[$name] = $value;
		}
		return $result;
	}
	
	/**
	 * Récupération d'une valeur REQUEST
	 * 
	 * @param array  $data     Tableau de REQUEST
	 * @param mixed  $default  Facultatif: valeur par défaut
	 * @param mixed  $hash     Nom de la variable dans laquelle sont stockées les valeurs
	 * @return mixed Valeur de la requête
	 */
	public static function getValue(&$data, $name, $default, $hash) {
		if (isset(self::$checked[$hash.$name])) {
			// On récupère la variable vérifiée des données
			return $data[$name];
		} else {
			if (isset($data[$name]) && !is_null($data[$name])) {
				// On filtre la variable requête pour la première fois
				$data[$name] = self::filter($data[$name]);
			} else if (!is_null($default)) {
				// On utilise la valeur par défaut
				$data[$name] = self::filter($default);
			} else {
				$data[$name] = null;
			}
			
			// La variable est vérifiée
			self::$checked[$hash.$name] = true;
			
			return $data[$name];
		}
	}
	
	/**
	 * Assignation d'une valeur Requête
	 * 
	 * @param string $name       Nom de la valeur
	 * @param mixed  $value      Valeur de la requête
	 * @param mixed  $hash       Hash des données dans lesquelles chercher
	 * @param bool   $overwrite  Réécrire par dessus si elle existe ?
	 * @return mixed Valeur précédente de la requête
	 */
	public static function set($name, $value, $hash = 'REQUEST', $overwrite = true) {
		// Vérification pour la réécriture
		if (!$overwrite && array_key_exists($name, $_REQUEST)) {
			return $_REQUEST[$name];
		}
		
		// Valeur précédente
		$previous = array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;
		
		switch (strtoupper($hash)) {
			case 'GET':
				$_GET[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'POST':
				$_POST[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'COOKIE':
				$_COOKIE[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'FILES':
				$_FILES[$name] = $value;
				break;
			default:
				$_REQUEST[$name] = $value;
				break;
		}
		
		// On met la var à SET
		self::$checked[$hash.$name] = true;
		
		return $previous;
	}
	
	/**
	 * Fonction de filtrage des variables de requête
	 * 
	 * @param mixed $variable Variable à filtrer
	 * @return Valeur filtrée
	 */
	public static function filter($variable) {
		if (is_array($variable)) {
			foreach ($variable as $key => $val) {
				$variable[$key] = self::filter($val);
			}
		} else {
			// On fait les manipulations de sécurité ici
			$variable = stripslashes($variable);
		}
		
		return $variable;
	}
}

?>