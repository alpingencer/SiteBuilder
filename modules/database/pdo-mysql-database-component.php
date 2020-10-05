<?php

namespace SiteBuilder\Database;

use PDO;
use PDOException;
use PDOStatement;

class PDOMySQLDatabaseComponent extends DatabaseComponent {
	private $pdo;

	public function connect(): void {
		$this->pdo = new PDO('mysql:host=' . $this->getServer() . ';dbname=' . $this->getName() . ';charset=utf8', $this->getUser(), $this->getPassword());
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	private function query(string $query): PDOStatement {
		try {
			return $this->pdo->query($query);
		} catch(PDOException $e) {
			$this->log($query, 'E');
			return new PDOStatement();
		}
	}

	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array {
		$query = "SELECT $columns FROM $table WHERE $primaryKey=$id";
		$statement = $this->query($query);

		if($statement->rowCount() == 0) {
			return array();
		} else if($statement->rowCount() > 1) {
			$this->log($query, 'E');
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
		$query = "SELECT $column FROM $table WHERE $primaryKey=$id";
		$statement = $this->query($query);

		if($statement->rowCount() == 0) {
			return '';
		} else if($statement->rowCount() > 1) {
			$this->log($query, 'E');
			return '';
		} else {
			return $statement->fetch(PDO::FETCH_NUM)[0];
		}
	}

	public function insert(string $table, array $values): bool {
		$fields = "";
		$fieldValues = "";

		foreach($values as $field => $value) {
			if($fields) {
				$fields .= ", ";
			}

			$fields .= "'" . $field . "'";

			if($fieldValues) {
				$fieldValues .= ", ";
			}

			$fieldValues .= $this->pdo->quote($value);
		}

		$query = "INSERT INTO $table ($fields) VALUES ($fieldValues)";
		$numAffectedRows = $this->pdo->exec($query);

		if($numAffectedRows === 0) {
			$this->log($query, "E");
			return false;
		} else {
			$this->log($query, "I");
			return true;
		}
		return false;
	}

	public function update(string $table, array $values, string $where): bool {
		$fieldsValues = "";

		foreach($values as $field => $value) {
			if($fieldsValues) {
				$fieldsValues .= ", ";
			}

			$fieldsValues .= "`" . $field . "`=" . $this->pdo->quote($value);
		}

		$query = "UPDATE $table SET $fieldsValues WHERE $where";
		$numAffectedRows = $this->pdo->exec($query);

		if($numAffectedRows === 0) {
			$this->log($query, "E");
			return false;
		} else {
			$this->log($query, "U");
			return true;
		}
	}

	public function delete(string $table, string $where): bool {
		$query = "DELETE FROM $table WHERE $where";
		$numAffectedRows = $this->pdo->exec($query);

		if($numAffectedRows === 0) {
			$this->log($query, "E");
			return false;
		} else {
			$this->log($query, "D");
			return true;
		}
	}

	public function log(string $query, string $type): bool {
		$date = date('Y-m-d H:i:s');

		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			// somebody is logged in
			$id = $_SESSION['__SiteBuilder_UserID'];
		} else {
			// nobody is logged in
			$id = 0;
		}

		$q2 = PDO::quote($query);
		$uri = PDO::quote($_SERVER['QUERY_STRING']);
		$q = "INSERT INTO log_db (UID,TIME,TYPE,PAGE,QUERY) VALUES ($id ,'$date','$type',$uri,$q2)";
		$numAffectedRows = $this->pdo->exec($q);
		return $numAffectedRows !== 0;
	}

	public function backupTables(string $tables = '*', string $fileName): void {
		$this->pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);

		// Script variables
		$compression = true;

		// Create / open files
		if($compression) {
			$zp = gzopen($fileName . '.sql.gz', "a9");
		} else {
			$handle = fopen($fileName . '.sql', 'a+');
		}

		// Array of all database field types which just take numbers
		$numTypes = array(
				'tinyint',
				'smallint',
				'mediumint',
				'int',
				'bigint',
				'float',
				'double',
				'decimal',
				'real'
		);

		// Get all of the tables
		if($tables == "*") {
			$tables = [];
			$pstm1 = $this->query('SHOW TABLES');
			while($row = $pstm1->fetch(PDO::FETCH_NUM)) {
				array_push($tables, $row[0]);
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}

		// Cycle through the tables
		foreach($tables as $table) {
			$result = $this->query("SELECT * FROM $table");
			$num_fields = $result->columnCount();
			$num_rows = $result->rowCount();

			$return = "";
			// Uncomment below if you want 'DROP TABLE IF EXISTS' displayed
			$return .= 'DROP TABLE IF EXISTS `' . $table . '`;';

			// Table structure
			$pstm2 = $this->query("SHOW CREATE TABLE $table");
			$row2 = $pstm2->fetch(PDO::FETCH_NUM);
			$ifnotexists = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
			$return .= "\n\n" . $ifnotexists . ";\n\n";

			if($compression) {
				gzwrite($zp, $return);
			} else {
				fwrite($handle, $return);
			}
			$return = "";

			// Insert values
			if($num_rows) {
				$return = 'INSERT INTO `' . "$table" . "` (";
				$pstm3 = $this->query("SHOW COLUMNS FROM $table");
				$count = 0;
				$type = array();
				while($rows = $pstm3->fetch(PDO::FETCH_NUM)) {
					if(stripos($rows[1], '(')) {
						$type[$table][] = stristr($rows[1], '(', true);
					} else
						$type[$table][] = $rows[1];

						$return .= "`" . $rows[0] . "`";
						$count++;
						if($count < ($pstm3->rowCount())) {
							$return .= ", ";
						}
				}
				$return .= ")" . ' VALUES';
				if($compression) {
					gzwrite($zp, $return);
				} else {
					fwrite($handle, $return);
				}
				$return = "";
			}
			$count = 0;
			while($row = $result->fetch(PDO::FETCH_NUM)) {
				$return = "\n\t(";
				for($j = 0; $j < $num_fields; $j++) {
					// $row[$j] = preg_replace("\n","\\n",$row[$j]);
					if(isset($row[$j])) {
						// If number, take away "". Else leave as string
						if((in_array($type[$table][$j], $numTypes)) && (!empty($row[$j])))
							$return .= $row[$j];
							else
								$return .= $this->pdo->quote($row[$j]);
					} else {
						$return .= 'NULL';
					}
					if($j < ($num_fields - 1)) {
						$return .= ',';
					}
				}
				$count++;
				if($count < ($result->rowCount())) {
					$return .= "),";
				} else {
					$return .= ");";
				}
				if($compression) {
					gzwrite($zp, $return);
				} else {
					fwrite($handle, $return);
				}
				$return = "";
			}
			$return = "\n\n-- ------------------------------------------------ \n\n";
			if($compression) {
				gzwrite($zp, $return);
			} else {
				fwrite($handle, $return);
			}
			$return = "";
		}

		if($compression) {
			gzclose($zp);
		} else {
			fclose($handle);
		}
	}

}