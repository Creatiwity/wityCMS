<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WDatabase.php 0000 25-10-2012 Fofif $
 * @package Wity
 */

class WDatabase extends PDO {
	private $tablePrefix = "";
	
	public function __construct($dsn, $user, $password) {
		if (!class_exists('PDO')) {
			throw new Exception("WSystem::getDB(): Class PDO unfound.");
		}
		
		try {
			# Bug de PHP5.3 : constante PDO::MYSQL_ATTR_INIT_COMMAND n'existe pas
			@parent::__construct($dsn, $user, $password);
		} catch (PDOException $e) {
			WNote::error('sql_conn_error', "Impossible to connect to MySQL.<br />".utf8_encode($e->getMessage()), 'custom');
			die;
		}
		$this->tablePrefix = WConfig::get('database.prefix');
	}
	
	public function prefixTables($querystring) {
		if (!empty($this->tablePrefix)) {
			// TODO
			// detect keywords AS to keep temporary tables unaffected
			
			// Replace vars prefixes
			$querystring = preg_replace('#\s([a-z0-9_]+)\.#i', $this->tablePrefix.'$1.', $querystring);
			
			// Replace table names
			$querystring = preg_replace('#(UPDATE|FROM)\s+([a-z0-9_]+)#i', '$1 '.$this->tablePrefix.'$2', $querystring);
		}
		return $querystring;
	}
	
	public function query($querystring) {
		return parent::query($this->prefixTables($querystring));
	}
	
	public function prepare($querystring, $driver_options = array()) {
		return parent::prepare($this->prefixTables($querystring), $driver_options);
	}
}

?>