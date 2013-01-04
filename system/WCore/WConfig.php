<?php 
/**
 * WConfig.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WConfig loads all configuration files, manages all configuration values
 *
 * @package System\WCore
 * @author xpLosIve
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-22-11-2012
 */
class WConfig {
	
    /**
     * @var array() Multidimensionnal array containing configurations sorted by type
     * 
     * No '.' in keys because it's a reserved character
     */
	private static $configs = array();
	
    /**
     * @var array() List of loaded configuration files
     */
	private static $files = array();

    /**
     * @var array() Stores modified configurations 
     */
	private static $modified = array();
	
	/**
	 * Returns configuration value
	 * 
	 * @param  string $path     configuration path
	 * @param  mixed  $default  optional default value
	 * @return mixed  configuration value related to $path
	 */
	public static function get($path, $default = null) {
		$result = $default;
		
		// Config nodes path
		if ($nodes = explode('.', $path)) {
			$config = &self::$configs;
			$path_count = count($nodes) - 1;
			
			// Running through configs
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
     * Assign a configuration value to a path
	 * 
	 * @param  string $path   configuration path
	 * @param  mixed  $value  configuration value
	 * @return mixed  configuration value
	 */
	public static function set($path, $value) {
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
		
		// Notifying configuration modification
		array_push(self::$modified, $nodes[0]);
	}
	
	/**
     * Adds configurations from a file
	 * 
	 * @param  string  $field configuration name
	 * @param  string  $file  configuration file
	 * @param  string  $type  file type
	 * @return boolean true if successful, false otherwise
	 */
	public static function load($field, $file, $type = '') {
		if (!is_file($file) || isset(self::$files[$field]) || strpos($field, '.') !== false) {
			return false;
		}
		
		// Find type using file extension
		if (empty($type)) {
			$type = substr($file, strrpos($file, '.') + 1);
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
			
			default:
				return false;
		}
		// Saving the file
		self::$files[$field] = array($file, $type);
		return true;
	}
	
	/**
	 * Destroys a configuration value
	 * 
	 * @param  string  $path configuration path
	 */
	public static function clear($path) {
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
		
		// Notifying configuration modification
		array_push(self::$modified, $nodes[0]);
	}
	
	/**
	 * Saves configurations
	 * 
	 * @param  string  $field configuration name
	 */
	public static function save($field) {
		if (in_array($field, self::$modified)) {
			list($file, $type) = self::$files[$field];
			
			switch (strtolower($type)) {
				case 'ini':
					if (is_writable(dirname($file))) {
						// Opening
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						foreach(self::$configs[$field] as $name => $value) {
							$data .= $name . ' = ' . $value ."\n";
						}
						
						// Writing
						fwrite($handle, $data);
						fclose($handle);
						
						// chmod editing
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'php':
					if (is_writable(dirname($file))) {
						// Opening
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						// Writing
						fwrite($handle, "<?php\n\n$".$field." = ".var_export(self::$configs[$field], true).";\n\n?>");
						fclose($handle);
						
						// chmod editing
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'xml':
					if (is_writable(dirname($file))) {
						// Openning
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						$data = '<?xml version="1.0" encoding="utf-8"?>' ."\n"
							  . '<configs>' ."\n";
						
						foreach(self::$configs[$field] as $name => $value) {
							$data .= '	<config name="' . $name . '">' . $value . '</config>' ."\n";
						}
						
						$data = '</configs>' ."\n";
						
						// Writing
						fwrite($handle, $data);
						fclose($handle);
						
						// chmod editing
						$chmod = chmod($file, 0777);
					}
					break;
				
				case 'json':
					if (is_writable(dirname($file))) {
						// Openning
						if (!($handle = fopen($file, 'w'))) {
							return false;
						}
						
						// Writing
						fwrite($handle, json_encode(self::$configs[$field]));
						fclose($handle);
						
						// chmod editing
						$chmod = chmod($file, 0777);
					}
					break;
			}
		}
	}
	
	/**
	 * Unloads a configuration
	 * 
	 * @param  string  $field configuartion name
	 */
	public static function unload($field) {
		unset(self::$configs[$field], self::$files[$field]);
	}
}

?>