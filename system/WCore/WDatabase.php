<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @version	$Id: WCore/WDatabase.php 0001 21-11-2012 Fofif $
 * @package Wity
 */

class WDatabase extends PDO {
	private $tablePrefix = "";
	private $tables = array();
	
	public function __construct($dsn, $user, $password) {
		if (!class_exists('PDO')) {
			throw new Exception("WSystem::__construct(): Class PDO not found.");
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
	
	/**
	 * Declare a new table in order to be automaticly prefixed
	 * 
	 * @param string $table Table's name
	 */
	public function declareTable($table) {
		$this->tables[] = $table;
	}
	
	/**
	 * Add prefix to table's name (see WDatabase::declareTable()) in a querystring
	 */
	private function prefixTables($querystring) {
		if (!empty($this->tablePrefix)) {
			foreach ($this->tables as $table) {
				$querystring = preg_replace('#([^a-z0-9_])'.$table.'([^a-z0-9_])#', '$1'.$this->tablePrefix.$table.'$2', $querystring);
			}
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