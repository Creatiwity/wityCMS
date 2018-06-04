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
 * @author Matthieu Raymond <matthieu.raymond@creatiwity.net>
 * @version 0.6.2-04-06-2018
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
				$message = WLang::get('error_could_not_find_pdo');
			}

			WNote::error('error_sql_conn_failed', WLang::get('error_sql_conn_failed', $message), 'debug, die');
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
				$querystring = preg_replace('#([^a-z\\\\0-9_])'.$table.'([^a-z0-9_]|$)#', '$1'.$this->tablePrefix.$table.'$2', $querystring);
			}
		}

		$querystring = str_replace('\\', '', $querystring);

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

	/**
	 * Insert a new row in table and return its id
	 *
	 * @param string        $table table name
	 * @param array(string) $fields fields to insert
	 * @param array(*)      $data data to insert (same key as in $fields)
	 *
	 * @return int or false id of inserted row or failure
	 */
	public function insertInto($table, $fields, $data) {
		$req = 'INSERT INTO `'.$table.'`(';

		foreach ($fields as $key) {
			$req .= $key.', ';
		}

		if (count($fields) >= 1) {
			$req = substr($req, 0, -2);
		}

		$req .= ') VALUES (';

		foreach ($fields as $key) {
			$req .= ':'.$key.', ';
		}

		if (count($fields) >= 1) {
			$req = substr($req, 0, -2);
		}

		$req .= ')';

		$prep = $this->prepare($req);

		foreach ($fields as $key) {
			$data[$key] = isset($data[$key]) ? $data[$key] : '';
			$prep->bindParam(':'.str_replace('\\', '', $key), $data[$key]);
		}

		if ($prep->execute()) {
			return $this->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * Update a table and return the numbers of row affected
	 *
	 * @param string        $table table name
	 * @param array(string) $fields fields to update
	 * @param array(*)      $data data to update (same key as in $fields)
	 * @param string        $cond condition(s) of update (all rows by default)
	 *
	 * @return int or false number of inserted row or failure
	 */
	public function update($table, $fields, $data, $cond = '1') {
		$req = 'UPDATE `'.$table.'` SET ';

		foreach ($fields as $key) {
			$req .= $key.' = :'.$key.', ';
		}

		if (count($fields) >= 1) {
			$req = substr($req, 0, -2);
		}

		$req .= ' WHERE '.$cond;

		$prep = $this->prepare($req);

		foreach ($fields as $key) {
			$data[$key] = isset($data[$key]) ? $data[$key] : '';
			$prep->bindParam(':'.str_replace('\\', '', $key), $data[$key]);
		}

		return $prep->execute();
	}
}

?>
