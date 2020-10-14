<?php

namespace SiteBuilder\Database;

use ErrorException;
use PDO;
use PDOException;
use PDOStatement;

class PDOMySQLDatabaseComponent extends DatabaseComponent {
	private $pdo;

	public function connect(string $server, string $name, string $user, string $password): void {
		try {
			$this->pdo = new PDO("mysql:host=$server;dbname=$name;charset=utf8", $user, $password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			throw new ErrorException("Failed while connecting to database! Server: '$server', Name: '$name', User: '$user', Password: '$password'");
		}
	}

	private function query(string $query): PDOStatement {
		try {
			return $this->pdo->query($query);
		} catch(PDOException $e) {
			$this->log('E', $query);
			return new PDOStatement();
		}
	}

	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array {
		$query = "SELECT $columns FROM $table WHERE `$primaryKey`='$id'";
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
		$statement = $this->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if($result === false) $result = array();
		return $result;
	}

	public function getVal(string $table, string $id, string $column, string $primaryKey = 'ID'): string {
		$query = "SELECT `$column` FROM $table WHERE `$primaryKey`='$id'";
		$statement = $this->query($query);

		if($statement->rowCount() == 0) {
			return '';
		} else if($statement->rowCount() > 1) {
			$this->log('E', $query);
			return '';
		} else {
			return $statement->fetch(PDO::FETCH_NUM)[0];
		}
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
		if(!$this->isLoggingEnabled()) return true;

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
