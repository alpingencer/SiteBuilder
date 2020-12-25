<?php

namespace SiteBuilder\Modules\Database\Controllers;

use SiteBuilder\Modules\Database\DatabaseController;
use ErrorException;
use PDO;
use PDOException;
use PDOStatement;

class MySQLDatabaseController extends DatabaseController {
	private $pdo;

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

	private function query(string $query): PDOStatement {
		try {
			$this->log('Q', $query);
			return $this->pdo->query($query);
		} catch(PDOException $e) {
			$this->log('E', $query);
			return new PDOStatement();
		}
	}

	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array {
		$query = "SELECT $columns FROM $table WHERE `$primaryKey`='$id'";
		return $this->getRowByQuery($query);
	}

	public function getRowByQuery(string $query): array {
		$statement = $this->query($query);

		if($statement->rowCount() === 0) {
			return array();
		} else if($statement->rowCount() > 1) {
			$this->log('E', $query);
			return array();
		} else {
			return $statement->fetch(PDO::FETCH_ASSOC);
		}
	}

	public function getRows(string $table, string $where, string $columns = '*', string $order = ''): array {
		$query = "SELECT $columns FROM $table WHERE $where";
		if(!empty($order)) $query .= " ORDER BY $order";
		return $this->getRowByQuery($query);
	}

	public function getRowsByQuery(string $query): array {
		$statement = $this->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if($result === false) $result = array();
		return $result;
	}

	public function getVal(string $table, string $id, string $column, string $primaryKey = 'ID'): string {
		$query = "SELECT `$column` FROM $table WHERE `$primaryKey`='$id'";
		return $this->getValByQuery($query);
	}

	public function getValByQuery(string $query): string {
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

	public function delete(string $table, string $where): int {
		$query = "DELETE FROM `$table` WHERE $where";
		$numAffectedRows = $this->pdo->exec($query);
		$this->log('D', $query);
		return $numAffectedRows;
	}

	public function log(string $type, string $query): bool {
		switch($type) {
			case 'Q':
				$minLoggingLevel = DatabaseController::LOGGING_ALL;
				break;
			case 'I':
			case 'U':
			case 'D':
				$minLoggingLevel = DatabaseController::LOGGING_MODIFY;
				break;
			case 'E':
				$minLoggingLevel = DatabaseController::LOGGING_ERROR;
				break;
		}

		// Check if current logging level is higher than minimum required level for current type
		// If no, return: No logging enabled
		if($minLoggingLevel > $this->getLoggingLevel()) {
			return true;
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

