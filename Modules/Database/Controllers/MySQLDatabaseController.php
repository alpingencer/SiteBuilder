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
 * @namespace SiteBuilder\Modules\Datbase\Controllers
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
	 * @return PDOStatement
	 */
	private function query(string $query): PDOStatement {
		try {
			$this->log('Q', $query);
			return $this->pdo->query($query);
		} catch(PDOException $e) {
			$this->log('E', $query);
			return new PDOStatement();
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRow()
	 */
	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array {
		$query = "SELECT $columns FROM `$table` WHERE `$primaryKey`='$id'";
		return $this->getRowByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRowByQuery()
	 */
	public function getRowByQuery(string $query): array {
		$statement = $this->query($query);

		// Check if no results are returned
		// If yes, throw error: Condition is too specific
		if($statement->rowCount() === 0) {
			$this->log('E', $query);
			throw new ErrorException('Get row returned no rows!');
		}

		// Check if multiple results are returned
		// If yes, throw error: Condition is not specific enough
		if($statement->rowCount() > 1) {
			$this->log('E', $query);
			throw new ErrorException('Get row returned multiple rows!');
			return array();
		}

		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRows()
	 */
	public function getRows(string $table, string $where, string $columns = '*', string $order = ''): array {
		$query = "SELECT $columns FROM `$table` WHERE $where";
		if(!empty($order)) $query .= " ORDER BY $order";
		return $this->getRowsByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getRowsByQuery()
	 */
	public function getRowsByQuery(string $query): array {
		$statement = $this->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);

		// Check if PDOStatement returned false
		// If yes, throw error: Something went wrong while fetching the result
		if($result === false) {
			throw new ErrorException('Error while getting rows by query!');
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getVal()
	 */
	public function getVal(string $table, string $id, string $column, string $primaryKey = 'ID') {
		$query = "SELECT `$column` FROM `$table` WHERE `$primaryKey`='$id'";
		return $this->getValByQuery($query);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::getValByQuery()
	 */
	public function getValByQuery(string $query) {
		$statement = $this->query($query);

		// Check if no results are returned
		// If yes, throw error: Condition is too specific
		if($statement->rowCount() === 0) {
			throw new ErrorException("Get value returned no rows!");
		}

		// Check if multiple results are returned
		// If yes, throw error: Condition is not specific enough
		if($statement->rowCount() > 1) {
			throw new ErrorException("Get value returned multiple rows!");
		}

		return $statement->fetch(PDO::FETCH_NUM)[0];
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::insert()
	 */
	public function insert(string $table, array $values, $primaryKey = 'ID'): int {
		$objectID = $this->query("SELECT MAX(DISTINCT `$primaryKey`) FROM $table")->fetch(PDO::FETCH_NUM)[0] + 1;
		$fields = "`$primaryKey`";
		$fieldValues = "$objectID";

		foreach($values as $field => $value) {
			$fields .= ", ";
			$fields .= "`" . $field . "`";

			if($fieldValues) {
				$fieldValues .= ", ";
			}

			$fieldValues .= $this->pdo->quote($value);
		}

		$query = "INSERT INTO `$table` ($fields) VALUES ($fieldValues)";
		$numAffectedRows = $this->pdo->exec($query);

		if($numAffectedRows === 0) {
			$this->log('E', $query);
		} else {
			$this->log('I', $query);
		}

		return $objectID;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::update()
	 */
	public function update(string $table, array $values, string $where): int {
		$fieldsValues = "";

		foreach($values as $field => $value) {
			if($fieldsValues) {
				$fieldsValues .= ", ";
			}

			$fieldsValues .= "`" . $field . "`=" . $this->pdo->quote($value);
		}

		$query = "UPDATE `$table` SET $fieldsValues WHERE $where";
		$numAffectedRows = $this->pdo->exec($query);

		$this->log('U', $query);
		return $numAffectedRows;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::delete()
	 */
	public function delete(string $table, string $where): int {
		$query = "DELETE FROM `$table` WHERE $where";
		$numAffectedRows = $this->pdo->exec($query);
		$this->log('D', $query);
		return $numAffectedRows;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Database\DatabaseController::log()
	 */
	public function log(string $type, string $query): bool {
		// Check if logging for the current type is enabled
		// If no, return: No logging enabled
		switch($type) {
			case 'Q':
				if($this->getLoggedQueryTypes() & DatabaseController::LOGGING_QUERY == 0) {
					return true;
				}
			case 'I':
			case 'U':
			case 'D':
				if($this->getLoggedQueryTypes() & DatabaseController::LOGGING_MODIFY == 0) {
					return true;
				}
			case 'E':
				if($this->getLoggedQueryTypes() & DatabaseController::LOGGING_ERROR == 0) {
					return true;
				}
		}

		$date = date('Y-m-d H:i:s');

		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			// somebody is logged in
			$id = $_SESSION['__SiteBuilder_UserID'];
		} else {
			// nobody is logged in
			$id = 0;
		}

		$query = $this->pdo->quote($query);
		$uri = $this->pdo->quote($_SERVER['REQUEST_URI']);
		$q = "INSERT INTO " . $this->getLogTableName() . " (UID,TIME,TYPE,PAGE,QUERY) VALUES ($id ,'$date','$type',$uri,$query)";
		$numAffectedRows = $this->pdo->exec($q);
		return $numAffectedRows === 1;
	}

}

