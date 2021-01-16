<?php

namespace SiteBuilder\Modules\Database\Controllers;

use SiteBuilder\Modules\Database\DatabaseController;
use ErrorException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * The MySQLDatabaseController provides out-of-the-box support for MySQL databases.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Database\Controllers
 * @see DatabaseController
 */
class MySQLDatabaseController extends DatabaseController {
	/**
	 * The PDO object used to connect and interface with the database
	 *
	 * @var PDO
	 */
	private $pdo;

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::connect()
	 */
	public function connect(): void {
		$server = $this->getServer();
		$name = $this->getName();
		$user = $this->getUser();
		$password = $this->getPassword();

		try {
			$this->pdo = new PDO("mysql:host=$server;dbname=$name;charset=utf8", $user, $password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			throw new ErrorException("Failed while connecting to database! Server: '$server', Name: '$name', User: '$user', Password: '$password'");
		}
	}

	/**
	 * Executes and logs a query, returning the resulting PDOStatement
	 *
	 * @param string $query The query to execute
	 * @param string $type The type of the query
	 * @return PDOStatement
	 */
	private function query(string $query, string $type): PDOStatement {
		try {
			$statement = $this->pdo->query($query);
			$this->log($query, $type);
			return $statement;
		} catch(PDOException $e) {
			$this->log($query, 'E');
			throw new ErrorException("Failed while executing the given query: $query");
		}
	}

	private function checkTableExists(string $table): void {
		// Get the table information from the information schema
		$query = "SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`='" . $this->getName() . "' AND `TABLE_NAME`='$table'";
		$tables = $this->getRowsByQuery($query);

		// Check if no rows were returned
		// If yes, throw error: Table does not exist in database
		if(count($tables) === 0) {
			throw new ErrorException("The given table '$table' does not exist in the database!");
		}

		// Check if multiple rows were returned
		// If yes, throw error: Table name is somehow duplicated
		if(count($tables) > 1) {
			throw new ErrorException("Duplicate table name '$table' detected in the database!");
		}
	}

	private function checkValueTypes(string $table, array $values, bool $coalesce_null = true): void {
		// Get column information from the information schema
		$query = "SELECT `COLUMN_NAME`,`DATA_TYPE`,`IS_NULLABLE`,`CHARACTER_MAXIMUM_LENGTH` FROM `INFORMATION_SCHEMA`.`COLUMNS` ";
		$query .= "WHERE `TABLE_SCHEMA`='" . $this->getName() . "' AND `TABLE_NAME`='$table'";
		$table_columns = $this->getRowsByQuery($query);

		// Check if each specified key has a corresponding column in the table
		// If no, throw error: Values contain invalid keys
		foreach($values as $key => $value) {
			$table_column_exists = false;

			foreach($table_columns as $table_column) {
				$column_name = $table_column['COLUMN_NAME'];
				if($column_name === $key) {
					$table_column_exists = true;
					break;
				}
			}

			if(!$table_column_exists) {
				throw new ErrorException("Value specified for non-existing column '$key' in table '$table'!");
			}
		}

		// Store a list of valid MySQL column types for each PHP variable type
		// @formatter:off
		$php_to_mysql_types = array(
				'boolean' => array('boolean'),
				'integer' => array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal'),
				'double' => array('float', 'double'),
				'string' => array('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'date', 'datetime', 'timestamp', 'time', 'year', 'int'),
		);
		// @formatter:on

		foreach($table_columns as $table_column) {
			// Get relevant information from the row
			$column_name = $table_column['COLUMN_NAME'];
			$column_data_type = $table_column['DATA_TYPE'];
			$column_is_nullable = $table_column['IS_NULLABLE'] === 'YES';
			$column_maximum_length = $table_column['CHARACTER_MAXIMUM_LENGTH'];

			// Check if a column key has a specified value in the given array
			$value_exists = array_key_exists($column_name, $values);

			// Check if no value or null-value is specified and the column is not nullable
			// If yes, throw error: Cannot insert null into non-nullable column
			if(((!$value_exists && $coalesce_null) || ($value_exists && $values[$column_name] === null)) && !$column_is_nullable) {
				throw new ErrorException("No value specified for non-nullable column '$column_name' in table '$table'!");
			}

			// Check if a value is specified
			// If no, continue: Column is nullable
			if(!$value_exists) {
				continue;
			}

			// Get type of PHP variable
			$value = $values[$column_name];
			$value_data_type = gettype($value);

			if($value_data_type !== 'NULL') {
				// Check if PHP variable type can be inserted into the database
				// If no, throw error: Invalid PHP variable type to insert
				if(!array_key_exists($value_data_type, $php_to_mysql_types)) {
					throw new ErrorException("Cannot insert PHP variable of type '$value_data_type' into database!");
				}

				// Check if PHP variable is compatible with MySQL column type
				// If no, throw error: Cannot insert incompatible variable into database
				if(!in_array($column_data_type, $php_to_mysql_types[$value_data_type])) {
					throw new ErrorException("Invalid value type '$value_data_type' specified for column '$column_name' in table '$table'!");
				}

				// Check if values for special cases are formatted correctly
				// If no, throw error: Some MySQL types require special formatting to be inserted into the database
				switch($column_data_type) {
					case 'date':
						$value_is_formatted_validly = (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value) === 1);
						break;
					case 'datetime':
						$value_is_formatted_validly = (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $value) === 1);
						break;
					case 'timestamp':
						$value_is_formatted_validly = (preg_match('/[0-9]{14}/', $value) === 1);
						break;
					case 'time':
						$value_is_formatted_validly = (preg_match('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', $value) === 1);
						break;
					case 'year':
						$num_digits = strlen($value);

						if($num_digits === 2) {
							$value_is_formatted_validly = true;
						} else if($num_digits === 4) {
							$int_value = (int) $value;
							$value_is_formatted_validly = (1900 < $int_value && $int_value < 2156) || $value === '0000';
						} else {
							$value_is_formatted_validly = false;
						}
						break;
					default:
						$value_is_formatted_validly = true;
						break;
				}

				if(!$value_is_formatted_validly) {
					throw new ErrorException("The given value '$value' is not formatted properly for the column '$column_name' in table '$table', expected '$column_data_type'!");
				}
			}

			// Check if a maximum character length is specified and the given string value is too
			// long
			// If yes, throw error: Value is too long
			if($column_maximum_length !== null && $value_data_type === 'string') {
				$value_length = strlen($value);
				if($value_length > $column_maximum_length) {
					throw new ErrorException("The specified string of length '$value_length' is too long for the column '$column_name' in table '$table', expected length '$column_maximum_length'!");
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getPrimaryKey()
	 */
	public function getPrimaryKey(string $table): string {
		// Check if table exists
		$this->checkTableExists($table);

		// Get the primary key from the information schema
		$query = "SELECT `COLUMN_NAME`,`DATA_TYPE` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='" . $this->getName() . "' AND `TABLE_NAME`='$table' AND `COLUMN_KEY`='PRI'";
		$primary_keys = $this->getRowsByQuery($query);

		// Check if no rows were returned
		// If yes, throw error: A primary key must be defined
		if(count($primary_keys) === 0) {
			throw new ErrorException("No primary key defined for table '$table'!");
		}

		// Check if multiple rows were returned
		// If yes, throw error: Only a single primary key must be defined
		if(count($primary_keys) > 1) {
			throw new ErrorException("Multiple primary keys defined for table '$table'!");
		}

		// Get relevant information from the result
		$primary_key = $primary_keys[0];
		$primary_key_name = $primary_key['COLUMN_NAME'];
		$primary_key_type = $primary_key['DATA_TYPE'];

		// Check if the primary key is of type 'int'
		// If no, throw error: Primary keys must be of type 'int'#
		if($primary_key_type !== 'int') {
			throw new ErrorException("The primary key '$primary_key_name' in table '$table' must be of type 'int', '$primary_key_type' given!");
		}

		// Return the primary key name
		return $primary_key_name;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getForeignKey()
	 */
	public function getForeignKey(string $table, string $referenced_table): string {
		// Check if table exists
		$this->checkTableExists($table);

		// Get the engine of the table from the information schema
		$table_engine = $this->getValByQuery("SELECT `ENGINE` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`='" . $this->getName() . "' AND `TABLE_NAME`='$table'");

		// Check if table engine is 'InnoDB'
		// If no, throw error: Cannot get foreign key if the table engine is not 'InnoDB'
		if($table_engine !== 'InnoDB') {
			throw new ErrorException("To get a foreign key, the table '$table' must use the engine 'InnoDB', '$table_engine' given!");
		}

		// Get the primary key of the referenced table
		$referenced_column = $this->getPrimaryKey($referenced_table);

		// Get the foreign key from the information schema
		$query = "SELECT `COLUMN_NAME` FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE ";
		$query .= "`REFERENCED_TABLE_SCHEMA`='" . $this->getName() . "' AND `REFERENCED_TABLE_NAME`='$referenced_table' AND `REFERENCED_COLUMN_NAME`='$referenced_column' AND `TABLE_NAME`='$table'";
		$foreign_keys = $this->getRowsByQuery($query);

		// Check if no rows were returned
		// If yes, throw error: No foreign key found
		if(count($foreign_keys) === 0) {
			throw new ErrorException("No foreign key referencing the primary key '$referenced_column' of table '$referenced_table' found in table '$table'!");
		}

		// Check if multiple rows were returned
		// If yes, throw error: Multiple foreign keys referencing the same column found
		if(count($foreign_keys) > 1) {
			throw new ErrorException("Multiple foreign keys referencing the primary key '$referenced_column' of table '$referenced_table' found in table '$table'!");
		}

		// Return the foreign key
		$foreign_key = $foreign_keys[0]['COLUMN_NAME'];
		return $foreign_key;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRow()
	 */
	public function getRow(string $table, int $id, string $columns = '*'): array {
		$primaryKey = $this->getPrimaryKey($table);
		$query = "SELECT $columns FROM `$table` WHERE `$primaryKey`='$id'";
		return $this->getRowByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRows()
	 */
	public function getRows(string $table, string $condition, string $columns = '*', string $order = ''): array {
		$query = "SELECT $columns FROM `$table` WHERE $condition";
		if(!empty($order)) $query .= " ORDER BY $order";
		return $this->getRowsByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getVal()
	 */
	public function getVal(string $table, int $id, string $column) {
		$primaryKey = $this->getPrimaryKey($table);
		$query = "SELECT `$column` FROM `$table` WHERE `$primaryKey`='$id'";
		return $this->getValByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRowByQuery()
	 */
	public function getRowByQuery(string $query): array {
		$statement = $this->query($query, 'Q');

		// Check if no results are returned
		// If yes, throw error: Condition is too specific
		if($statement->rowCount() === 0) {
			$this->log($query, 'E');
			throw new ErrorException("Get row returned no rows! Query: '$query'");
		}

		// Check if multiple results are returned
		// If yes, throw error: Condition is not specific enough
		if($statement->rowCount() > 1) {
			$this->log($query, 'E');
			throw new ErrorException("Get row returned multiple rows! Query: '$query'");
		}

		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRowsByQuery()
	 */
	public function getRowsByQuery(string $query): array {
		$statement = $this->query($query, 'Q');
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);

		// Check if PDOStatement returned false
		// If yes, throw error: Something went wrong while fetching the result
		if($result === false) {
			throw new ErrorException("Error while getting rows by query! QUery: '$query'");
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getValByQuery()
	 */
	public function getValByQuery(string $query) {
		$statement = $this->query($query, 'Q');

		// Check if no results are returned
		// If yes, throw error: Condition is too specific
		if($statement->rowCount() === 0) {
			throw new ErrorException("Get value returned no rows! Query: '$query'");
		}

		// Check if multiple results are returned
		// If yes, throw error: Condition is not specific enough
		if($statement->rowCount() > 1) {
			throw new ErrorException("Get value returned multiple rows! Query: '$query'");
		}

		return $statement->fetch(PDO::FETCH_NUM)[0];
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::insert()
	 */
	public function insert(string $table, array $values): int {
		// Get the primary key of the table
		$primary_key = $this->getPrimaryKey($table);

		// Check if the primary key has a defined value in the given values array
		// If yes, throw error: The primary key is managed by SiteBuilder and should not be
		// specified
		if(array_key_exists($primary_key, $values)) {
			throw new ErrorException("Cannot specify a value for the primary key '$primary_key' when inserting into the database!");
		}

		// Get next ID by incrementing by 1
		$query = "SELECT MAX(DISTINCT `$primary_key`) FROM `$table`";
		$id = $this->getValByQuery($query) + 1;

		// Merge the id value into the values array
		$values = array_merge(array(
				$primary_key => $id
		), $values);

		// Check if values are of compatible type with the table
		$this->checkValueTypes($table, $values, true);

		// Build the key part of the SQL string from the given keys
		$keys_as_string = implode(', ', array_map(function (string $key) {
			return "`$key`";
		}, array_keys($values)));

		// Build the value part of the SQL string from the given values
		$values_as_string = implode(',', array_map(function ($value) {
			if($value === null) {
				return "NULL";
			} else {
				return "'$value'";
			}
		}, array_values($values)));

		// Build the complete query and execute it
		$query = "INSERT INTO `$table`($keys_as_string) VALUES ($values_as_string)";
		$this->query($query, 'I');

		// Return the object ID
		return $id;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::update()
	 */
	public function update(string $table, array $values, string $condition): void {
		// Get the primary key of the table
		$primary_key = $this->getPrimaryKey($table);

		// Check if the primary key has a defined value in the given values array
		// If yes, throw error: The primary key is managed by SiteBuilder and should not be
		// specified
		if(array_key_exists($primary_key, $values)) {
			throw new ErrorException("Cannot specify a value for the primary key '$primary_key' when updating data in the database!");
		}

		// Check if values are of compatible type with the table
		$this->checkValueTypes($table, $values, false);

		// Build the set values part of the SQL string from the given values
		$values_as_string = implode(',', array_map(function (string $key) use ($values) {
			$value = $values[$key];
			if($value === null) {
				return "`$key`=NULL";
			} else {
				return "`$key`='$value'";
			}
		}, array_keys($values)));

		// Build the complete query and execute it
		$query = "UPDATE `$table` SET $values_as_string WHERE $condition";
		$this->query($query, 'U');
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::delete()
	 */
	public function delete(string $table, string $condition): void {
		// Check if table exists
		$this->checkTableExists($table);

		// Run delete query
		$query = "DELETE FROM `$table` WHERE $condition";
		$this->query($query, 'D');
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::log()
	 */
	public function log(string $query, string $type): void {
		// Check if logging for the current type is enabled
		// If no, return: No logging enabled
		switch($type) {
			case 'Q':
				if(($this->getLoggedQueryTypes() & DatabaseController::LOGGING_QUERY) == 0) {
					return;
				}
			case 'I':
			case 'U':
			case 'D':
				if(($this->getLoggedQueryTypes() & DatabaseController::LOGGING_MODIFY) == 0) {
					return;
				}
			case 'E':
				if(($this->getLoggedQueryTypes() & DatabaseController::LOGGING_ERROR) == 0) {
					return;
				}
		}

		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			// somebody is logged in
			$uid = $_SESSION['__SiteBuilder_UserID'];
		} else {
			// nobody is logged in
			$uid = 0;
		}

		$date = date('Y-m-d H:i:s');
		$uri = $_SERVER['REQUEST_URI'];

		$q = "INSERT INTO `" . $this->getLogTableName() . "`(`UID`,`TIME`,`TYPE`,`PAGE`,`QUERY`) VALUES (?,?,?,?,?)";
		$statement = $this->pdo->prepare($q);
		$statement->execute(array($uid,$date,$type,$uri,$query));
	}

}

