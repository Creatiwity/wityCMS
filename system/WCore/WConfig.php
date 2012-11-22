<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version   $Id: WCore/WConfig.php 0003 13-05-2010 xplosive $
 * @desc      Gestion des variables de configuration
 */

class WConfig {
	// Array multidimensionnel contenant les configurations classées par type
	// Pas de '.' dans les clés car il est réservé
	private static $configs = array();
	
	// Les fichiers de configuration chargés
	private static $files = array();
	
	// Enregistre les configurations modifiées
	private static $modified = array();
	
	/**
	 * Retourne la valeur d'une configuration
	 * 
	 * @access public
	 * @param  string $path     Chemin de la config
	 * @param  mixed  $default  Facultative: valeur par défaut de la config
	 * @return mixed  Valeur de la config associée au path
	 */
	public static function get($path, $default = null) {
		$result = $default;
		
		// Noeuds du chemin de la config
		if ($nodes = explode('.', $path)) {
			$config = &self::$configs;
			$path_count = count($nodes) - 1;
			
			// On parcourt les configs
			for ($i = 0; $i < $path_count; $i++) {
				if (isset($config[$nodes[$i]])) {
					$config = &$config[$nodes[$i]];
				} else {
					break;
				}
			}
			
			if (isset($config[$nodes[$i]])) {
				$result = $config[$nodes[$i]];
			}
		}
		
		return $result;
	}
	
	/**
	 * Assigne une valeur de configuration
	 * 
	 * @access public
	 * @param  string $path   Chemin de la config
	 * @param  mixed  $value  Valeur de la config
	 * @return mixed  Valeur de la config
	 */
	public static function set($path, $value) {
		// Explose le path en array
		$nodes = explode('.', $path);
		
		$config = &self::$configs;
		$path_count = sizeof($nodes)-1;
		for ($i = 0; $i < $path_count; $i++) {
			if (!isset($config[$nodes[$i]])) {
				$config[$nodes[$i]] = array();
			}
			$config = &$config[$nodes[$i]];
		}
		
		$config[$nodes[$i]] = $value;
		
		// On précise que la config a été modifiée
		array_push(self::$modified, $nodes[0]);
	}
	
	/**
	 * Ajout de configurations depuis un fichier
	 * 
	 * @param  string  $field nom de la config
	 * @param  string  $file  fichier de config
	 * @param  string  $type  type du fichier de config
	 * @return boolean
	 */
	public static function load($field, $file, $type = 'php') {
		if (!is_file($file) || isset(self::$files[$field])) {
			return false;
		}
		
		switch(strtolower($type)) {
			case 'ini':
				self::$configs[$field] = parse_ini_file($file, true);
				break;
			
			case 'php':
				include_once $file;
				if (isset(${$field})) {
					self::$configs[$field] = ${$field};
				}
				unset(${$field});
				break;
			
			case 'xml':
				self::$configs[$field] = simplexml_load_file($file);
				break;
			
			case 'json':
				self::$configs[$field] = json_decode(file_get_contents($file), true);
				break;
		}
		// Enregistrement du fichier
		self::$files[$field] = array($file, $type);
		return true;
	}
	
	/**
	 * Supprime une valeur de configuration
	 * 
	 * @param  string  $path chemin de la config
	 * @return void
	 */
	public static function clear($path) {
		// Explose le path en array
		$nodes = explode('.', $path);
		
		$config = &self::$configs;
		$path_count = sizeof($nodes)-1;
		$exists = true;
		for ($i = 0; $i < $path_count; $i++) {
			if (isset($config[$nodes[$i]])) {
				$config = &$config[$nodes[$i]];
			} else {
				$exists = false;
				break;
			}
		}
		
		if ($exists) {
			unset($config[$nodes[$i]]);
		}
		
		// On précise que la config a été modifiée
		array_push(self::$modified, $nodes[0]);
	}
	
	/**
	 * Enregistrement des configurations
	 * 
	 * @param  string  $field nom de la config
	 */
	public static function save($field) {
		if (in_array($field, self::$modified)) {
			list($file, $type) = self::$files[$field];
			
			switch (strtolower($type)) {
				case 'ini':
					if (is_writable(dirname($file))) {
						// Ouverture
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						foreach(self::$configs[$field] as $name => $value) {
							$data .= $name . ' = ' . $value ."\n";
						}
						
						// Ecriture
						fwrite($handle, $data);
						fclose($handle);
						
						// Modification du chmod
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'php':
					if (is_writable(dirname($file))) {
						// Ouverture
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						// Ecriture
						fwrite($handle, "<?php\n\n$".$field." = ".var_export(self::$configs[$field], true).";\n\n?>");
						fclose($handle);
						
						// Modification du chmod
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'xml':
					if (is_writable(dirname($file))) {
						// Ouverture
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						$data = '<?xml version="1.0" encoding="utf-8"?>' ."\n"
							  . '<configs>' ."\n";
						
						foreach(self::$configs[$field] as $name => $value) {
							$data .= '	<config name="' . $name . '">' . $value . '</config>' ."\n";
						}
						
						$data = '</configs>' ."\n";
						
						// Ecriture
						fwrite($handle, $data);
						fclose($handle);
						
						// Modification du chmod
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'json':
					if (is_writable(dirname($file))) {
						// Ouverture
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						// Ecriture
						fwrite($handle, json_encode(self::$configs[$field]));
						fclose($handle);
						
						// Modification du chmod
						$chmod = chmod($file, 0777);
					}
					break;
			}
		}
	}
	
	/**
	 * Déchargement d'une config (peut être utile...)
	 * 
	 * @param  string  $field nom de la config
	 */
	public static function unload($field) {
		unset(self::$configs[$field], self::$files[$field]);
	}
}

?>