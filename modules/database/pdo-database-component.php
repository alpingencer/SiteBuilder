<?php

namespace SiteBuilder\Database;

use PDO;
use PDOException;

class PDODatabaseComponent extends DatabaseComponent {
	private $pdo;

	public function connect(): void {
		$this->pdo = new PDO('mysql:host=' . $this->getServer() . ';dbname=' . $this->getName() . ';charset=utf8', $this->getUser(), $this->getPassword());
	}

	public function query(string $query) {
		try {
			$stmt = $this->pdo->query($query);
			return $stmt;
		} catch(PDOException $e) {
			error_log($e->getMessage());
			$this->pdo->log($stmt->queryString, "E");
			return null;
		}
	}

	public function getRow(string $query) {
		$result = $this->query($query);
		if($result == null || ($num_rows = $result->rowCount()) == 0) {
			return null;
		} else if($num_rows > 1) {
			$this->log($query, "E");
			return null;
		} else {
			return $result->fetch(PDO::FETCH_ASSOC);
		}
	}

	public function getVal(string $query) {
		$result = $this->query($query);
		if($result == null || ($num_rows = $result->rowCount()) == 0) {
			return null;
		} else if($num_rows > 1) {
			$this->log($query, "E");
			return null;
		} else {
			return $result->fetch(PDO::FETCH_NUM)[0];
		}
	}

	public function insert(string $table, array $values) {
		$fields = "";
		$fieldValues = "";

		foreach($values as $field => $value) {
			if($fields) {
				$fields .= ", ";
			}

			$fields .= "`" . $field . "`";

			if($fieldValues) {
				$fieldValues .= ", ";
			}

			// Handle zero dates
			if(strcmp($value, "0000-00-00") == 0) {
				$fieldValues .= "NULL";
			} else {
				$fieldValues .= $this->pdo->quote($value);
			}
		}

		$query = "INSERT INTO $table ($fields) VALUES ($fieldValues)";
		$ar = $this->pdo->exec($query);

		if($ar === true) {
			$this->log($query, "E");
			return null;
		} else {
			$this->log($query, "I");
			return $ar;
		}
	}

	public function update(string $table, array $values, string $where) {
		$varsvalues = "";

		foreach($values as $field => $value) {
			if($varsvalues) {
				$varsvalues .= ", ";
			}

			// Handle zero dates
			if($value === "0000-00-00") {
				$varsvalues .= "`" . $field . "`=NULL";
			} else {
				$varsvalues .= "`" . $field . "`=" . $this->pdo->quote($value);
			}
		}

		$query = "UPDATE $table SET $varsvalues WHERE $where";
		$ar = $this->pdo->exec($query);

		if($ar === false) {
			$this->log($query, "E");
			return NULL;
		} else {
			$this->log($query, "U");
			return $ar;
		}
	}

	public function delete(string $table, string $where) {
		$query = "DELETE FROM $table WHERE $where";
		$ar = $this->pdo->exec($query);

		if($ar === false) {
			$this->log($query, "E");
			return null;
		} else {
			$this->log($query, "D");
			return $ar;
		}
	}

	public function log(string $query, string $type) {
		$date = date('Y-m-d H:i:s');

		if(isset($_SESSION['SiteBuilder_User_ID'])) {
			// somebody is logged in
			$id = $_SESSION['SiteBuilder_User_ID'];
		} else {
			// nobody is logged in
			$id = 0;
		}

		$q2 = PDO::quote($query);
		$uri = PDO::quote($_SERVER['QUERY_STRING']);
		$q = "INSERT INTO log_db (UID,TIME,TYPE,PAGE,QUERY) VALUES ($id ,'$date','$type',$uri,$q2)";
		$this->pdo->exec($q);
	}

	public function backupTables(string $tables = '*', string $fileName) {
		$this->pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);

		// Script Variables
		$compression = true;

		// create/open files
		if($compression) {
			$zp = gzopen($fileName . '.sql.gz', "a9");
		} else {
			$handle = fopen($fileName . '.sql', 'a+');
		}

		// array of all database field types which just take numbers
		$numtypes = array(
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

		// get all of the tables
		if($tables == "*") {
			$tables = [];
			$pstm1 = $this->query('SHOW TABLES');
			while($row = $pstm1->fetch(PDO::FETCH_NUM)) {
				array_push($tables, $row[0]);
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}

		// cycle through the table(s)
		foreach($tables as $table) {
			$result = $this->query("SELECT * FROM $table");
			$num_fields = $result->columnCount();
			$num_rows = $result->rowCount();

			$return = "";
			// uncomment below if you want 'DROP TABLE IF EXISTS' displayed
			$return .= 'DROP TABLE IF EXISTS `' . $table . '`;';

			// table structure
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

			// insert values
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
						// if number, take away "". else leave as string
						if((in_array($type[$table][$j], $numtypes)) && (!empty($row[$j])))
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