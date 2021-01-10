<?php

namespace SiteBuilder\Modules\Database;

/**
 * The DatabaseController class defines abstract methods to interface with a database.
 * By extending and implementing these methods, the DatabaseModule will be able to interface with
 * a new type of database. Out of the box, SiteBuilder comes with support for MySQL via the
 * MySQLDatabaseController class.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Database
 * @see DatabaseModule
 * @see MySQLDatabaseController
 */
abstract class DatabaseController {
	/**
	 * The logging level constants are used with the DatabaseController::setLoggingLevel() method.
	 * Each logging level will enable a certain type of query to be logged. To enable or disable
	 * multiple logging types, bitwise operators can then be used.
	 *
	 * @var LOGGING_NONE integer Logs nothing
	 * @var LOGGING_ERROR integer Logs querries that returned errors
	 * @var LOGGING_MODIFY integer Logs querries that modified the database
	 * @var LOGGING_QUERY integer Logs all querries
	 * @var LOGGING_ALL integer Logs everything
	 * @see DatabaseController::setLoggingLevel()
	 * @see DatabaseController::$loggingLevel
	 */
	const LOGGING_NONE = 0;
	const LOGGING_ERROR = 1;
	const LOGGING_MODIFY = 2;
	const LOGGING_QUERY = 4;
	const LOGGING_ALL = 255;
	/**
	 * The ip address or host name of the server hosting the database
	 *
	 * @var string
	 */
	private $server;
	/**
	 * The name of the database to connect to
	 *
	 * @var string
	 */
	private $name;
	/**
	 * The username to use when connecting to the database
	 *
	 * @var string
	 */
	private $user;
	/**
	 * The password to use when connecting to the database
	 *
	 * @var string
	 */
	private $password;
	/**
	 * The types of querries that are logged.
	 * Each bit in the integer corresponds to one type of logged queries.
	 *
	 * @var int
	 */
	private $loggedQueryTypes;
	/**
	 * The table name in the database to log the queries to
	 *
	 * @var string
	 */
	private $logTableName;

	/**
	 * Returns an instance of DatabaseController
	 *
	 * @param string $server The server to connect to
	 * @param string $name The database to connect to
	 * @param string $user The username to connect with
	 * @param string $password The password to connect with
	 * @return DatabaseController The initialized instance
	 */
	public final static function init(string $server, string $name, string $user, string $password): DatabaseController {
		return new static($server, $name, $user, $password);
	}

	/**
	 * Constructor for the DatabaseController.
	 * To get an instance of this class, use DatabaseController::init()
	 *
	 * @param string $server The server to connect to
	 * @param string $name The database to connect to
	 * @param string $user The username to connect with
	 * @param string $password The password to connect with
	 * @see DatabaseController::init()
	 */
	private final function __construct(string $server, string $name, string $user, string $password) {
		$this->setServer($server);
		$this->setName($name);
		$this->setUser($user);
		$this->setPassword($password);
		$this->setLoggedQueryTypes(DatabaseController::LOGGING_NONE);
	}

	/**
	 * Connects to the database with the given authentication parameters
	 */
	public abstract function connect(): void;

	/**
	 * Infers the column name of the primary key of a given table from the database structure
	 *
	 * @param string $table The table to get the primary key of
	 * @return string The primary key of the table
	 */
	public abstract function getPrimaryKey(string $table): string;

	/**
	 * Infers the name of the foreign key of a table that references the primary key of another
	 * table
	 *
	 * @param string $table The table linking to another table
	 * @param string $referenced_table The table being linked to
	 * @return string The foreign key of the linking table
	 */
	public abstract function getForeignKey(string $table, string $referenced_table): string;

	/**
	 * Fetches a single row from the database
	 *
	 * @param string $table The table to fetch from
	 * @param int $id The unique ID of the row to fetch
	 * @param string $columns The keys of the columns to fetch
	 * @return array The fetched result
	 */
	public abstract function getRow(string $table, int $id, string $columns = '*'): array;

	/**
	 * Fetches multiple rows from the database
	 *
	 * @param string $table The table to fetch from
	 * @param string $condition The condition to filter the rows by
	 * @param string $columns The keys of the columns to fetch
	 * @param string $order The key to sort the rows by
	 * @return array The fetched result
	 */
	public abstract function getRows(string $table, string $condition, string $columns = '*', string $order = ''): array;

	/**
	 * Fetches a single value from the database
	 *
	 * @param string $table The table to fetch from
	 * @param int $id The unique ID of the row to fetch from
	 * @param string $column The key of the column to fetch
	 * @return mixed The fetched result
	 */
	public abstract function getVal(string $table, int $id, string $column);

	/**
	 * Fetches a signle row from the database with a custom query
	 *
	 * @param string $query The query to execute
	 * @return array The fetched result
	 */
	public abstract function getRowByQuery(string $query): array;

	/**
	 * Fetches multiple rows from the database with a custom query
	 *
	 * @param string $query The query to execute
	 * @return array The fetched result
	 */
	public abstract function getRowsByQuery(string $query): array;

	/**
	 * Fetches a single value from the database with a custom query
	 *
	 * @param string $query The query to execute
	 */
	public abstract function getValByQuery(string $query);

	/**
	 * Inserts an array of values into a table
	 *
	 * @param string $table The table to insert into
	 * @param array $values An associative array of key-value pairs to insert
	 * @return int The number of inserted rows
	 */
	public abstract function insert(string $table, array $values): int;

	/**
	 * Updates multiple rows in a table
	 *
	 * @param string $table The table to update
	 * @param array $values An associative array of key-value pairs to insert
	 * @param string $condition The condition to filter the rows by
	 * @return int The number of affected rows
	 */
	public abstract function update(string $table, array $values, string $condition): void;

	/**
	 * Deletes multiple rows from the database
	 *
	 * @param string $table The table to delete from
	 * @param string $condition The condition to filter the rows by
	 * @return int The number of deleted rows
	 */
	public abstract function delete(string $table, string $condition): void;

	/**
	 * Logs a query into the log table
	 *
	 * @param string $query The query to log
	 * @param string $type The type of the query
	 * @return bool Wether the operation was successful
	 */
	public abstract function log(string $query, string $type): void;

	/**
	 * Getter for the hosting server
	 *
	 * @return string
	 * @see DatabaseController::$server
	 */
	public final function getServer(): string {
		return $this->server;
	}

	/**
	 * Setter for the hosting server
	 *
	 * @param string $server
	 * @see DatabaseController::$server
	 */
	private final function setServer(string $server): void {
		$this->server = $server;
	}

	/**
	 * Getter for the database name
	 *
	 * @return string
	 * @see DatabaseController::$name
	 */
	public final function getName(): string {
		return $this->name;
	}

	/**
	 * Setter for the database name
	 *
	 * @param string $name
	 * @see DatabaseController::$name
	 */
	private final function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Getter for the username
	 *
	 * @return string
	 * @see DatabaseController::$user
	 */
	public final function getUser(): string {
		return $this->user;
	}

	/**
	 * Setter for the username
	 *
	 * @param string $user
	 * @see DatabaseController::$user
	 */
	private final function setUser(string $user): void {
		$this->user = $user;
	}

	/**
	 * Getter for the password
	 *
	 * @return string
	 * @see DatabaseController::$password
	 */
	public final function getPassword(): string {
		return $this->password;
	}

	/**
	 * Setter for the password
	 *
	 * @param string $password
	 * @see DatabaseController::$password
	 */
	private final function setPassword(string $password): void {
		$this->password = $password;
	}

	/**
	 * Getter for the logged query types
	 *
	 * @return int
	 * @see DatabaseController::$loggedQueryTypes
	 */
	public final function getLoggedQueryTypes(): int {
		return $this->loggedQueryTypes;
	}

	/**
	 * Setter for the logged query types
	 *
	 * @param int $loggedQueryTypes
	 * @param string $logTableName The name of the table to log to
	 * @return self Returns itself for chaining other functions
	 * @see DatabaseController::$loggedQueryTypes
	 */
	public function setLoggedQueryTypes(int $loggedQueryTypes, string $logTableName = '__log'): self {
		$this->loggedQueryTypes = $loggedQueryTypes;

		if($loggedQueryTypes > 0) {
			if(!isset($this->logTableName)) $this->setLogTableName($logTableName);
		} else {
			unset($this->logTableName);
		}

		return $this;
	}

	/**
	 * Getter for the table name to log to
	 *
	 * @return string
	 * @see DatabaseController::$logTableName
	 */
	public final function getLogTableName(): string {
		return $this->logTableName;
	}

	/**
	 * Setter for the table name to log to
	 *
	 * @param string $logTableName
	 * @see DatabaseController::$logTableName
	 */
	private final function setLogTableName(string $logTableName): void {
		$this->logTableName = $logTableName;
	}

}

