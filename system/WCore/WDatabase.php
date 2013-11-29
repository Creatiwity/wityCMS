<?php 
/**
 * WDatabase.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * WDatabase manages all database interactions.
 * 
 * @package System\WCore
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-29-11-2013
 */
class WDatabase extends PDO {
	/**
	 * @var string Stores the table prefix which is used in the database 
	 */
	private $tablePrefix = "";
	
	/**
	 * @var array List of all tables that will be automatically prefixed 
	 */
	private $tables = array();
	
	/**
	 * Opens the PDO connection with the database.
	 * 
	 * @param string $dsn database server
	 * @param string $user username
	 * @param string $password password
	 * @throws Exception
	 */
	public function __construct($dsn, $user, $password) {
		if (!class_exists('PDO')) {
			throw new Exception("WDatabase::__construct(): Class PDO not found.");
		}
		
		try {
			# Bug in PHP5.3 : PDO::MYSQL_ATTR_INIT_COMMAND constant does not exist
			@parent::__construct($dsn, $user, $password);
		} catch (PDOException $e) {
			$message = utf8_encode($e->getMessage());
			if ($message == "could not find driver") {
				$message = "WityCMS was unable to find the PHP's <strong>PDO extension</strong> on your system. Please, activate PDO to run the script.";
			}
			
			WNote::error('sql_conn_error', "Impossible to connect to the database MySQL.<br />".$message, 'debug, die');
		}
		
		$this->tablePrefix = WConfig::get('database.prefix');
	}
	
	/**
	 * Declare a new table in order to be automatically prefixed.
	 * 
	 * @param string $table table's name
	 */
	public function declareTable($table) {
		$this->tables[] = $table;
	}
	
	/**
	 * Transforms a query into another query with prefixed tables.
	 * 
	 * @param type $querystring query without prefix
	 * @return string query with all tables contained in the $table private property prefixed
	 */
	private function prefixTables($querystring) {
		if (!empty($this->tablePrefix)) {
			foreach ($this->tables as $table) {
				$querystring = preg_replace('#([^a-z0-9_])'.$table.'([^a-z0-9_]|$)#', '$1'.$this->tablePrefix.$table.'$2', $querystring);
			}
		}
		
		return $querystring;
	}
	
	/**
	 * Automatically maps created_date, created_by and modified_date, modified_by fields in a request.
	 * 
	 * Whenever an INSERT or UPDATE request is sent, this function will slightly modify the request to 
	 * update two default columns: created_date and created_by for an INSERT request or
	 * modified_date and modified_by for an UPDATE request.
	 * 
	 * @param string $querystring
	 * @return string
	 */
	private function setupDefaultFields($querystring) {
		$querystring = trim($querystring);
		
		if (preg_match('#^UPDATE#', $querystring)) {
			$querystring = str_replace('SET', 'SET modified_by = '.(isset($_SESSION['userid']) ? $_SESSION['userid'] : 0).', ', $querystring);
		} else if (preg_match('#^INSERT#', $querystring)) {
			$fields = '';
			$values = '';
			
			if (strpos($querystring, 'created_date') === false) {
				$fields = '`created_date`, ';
				$values = 'NOW(), ';
			}
			
			if (strpos($querystring, 'created_by') === false) {
				$fields .= '`created_by`, ';
				$values .= (isset($_SESSION['userid']) ? $_SESSION['userid'] : 0).', ';
			}
			
			if (!empty($fields)) {
				$querystring = preg_replace('#^INSERT INTO `?([a-zA-Z0-9_]+)`?\s*\((.+)\)#', 'INSERT INTO `$1` ('.$fields.'$2)', $querystring);
				$querystring = preg_replace('#VALUES\s*\(#', 'VALUES('.$values, $querystring);
			}
		}
		
		return $querystring;
	}
	
	/**
	 * Executes the query and returns the response.
	 * 
	 * @param string $querystring
	 * @return PDOStatement|false a PDOStatement object or false if an error occurred
	 */
	public function query($querystring) {
		$querystring = $this->setupDefaultFields($querystring);
		$querystring = $this->prefixTables($querystring);
		
		return parent::query($querystring);
	}
	
	/**
	 * Prepares a statement for execution and returns a statement object.
	 * 
	 * @todo Catch the eventual exception thrown by PDO::prepare
	 * 
	 * @param string $querystring the query that will be prepared
	 * @param string $driver_options optional list of key=>value pairs
	 * @return PDOStatement|false a PDOStatement object or false if an error occurred
	 */
	public function prepare($querystring, $driver_options = array()) {
		$querystring = $this->setupDefaultFields($querystring);
		$querystring = $this->prefixTables($querystring);
		
		return parent::prepare($querystring, $driver_options);
	}
}

?>
