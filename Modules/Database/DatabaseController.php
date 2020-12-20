<?php

namespace SiteBuilder\Modules\Database;

use ErrorException;

abstract class DatabaseController {
	const LOGGING_NONE = 0;
	const LOGGING_ERROR = 1;
	const LOGGING_MODIFY = 2;
	const LOGGING_ALL = 3;
	private $server;
	private $name;
	private $user;
	private $password;
	private $loggingLevel;
	private $logTableName;

	public final static function init(string $server, string $name, string $user, string $password): DatabaseController {
		return new static($server, $name, $user, $password);
	}

	private final function __construct(string $server, string $name, string $user, string $password) {
		$this->setServer($server);
		$this->setName($name);
		$this->setUser($user);
		$this->setPassword($password);
		$this->setLoggingLevel(DatabaseController::LOGGING_NONE);
	}

	public abstract function connect(): void;

	public abstract function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array;

	public abstract function getRows(string $table, string $where, string $columns = '*', string $order = ''): array;

	public abstract function getVal(string $table, string $id, string $column, string $primaryKey = 'ID'): string;

	public function getValByCondition(string $table, string $where, string $column): string {
		$rows = $this->getRows($table, $where);

		// Check if no results are returned
		// If yes, throw error: Condition is too specific
		if(sizeof($rows) === 0) {
			throw new ErrorException("Get value condition returned no rows!");
		}

		// Check if multiple results are returned
		// If yes, throw error: Condition is not specific enough
		if(sizeof($rows) > 1) {
			throw new ErrorException("Get value condition returned multiple rows!");
		}

		return $rows[0][$column];
	}

	public abstract function insert(string $table, array $values, $primaryKey = 'ID'): int;

	public abstract function update(string $table, array $values, string $where): int;

	public abstract function delete(string $table, string $where): int;

	public abstract function log(string $type, string $query): bool;

	public final function getServer(): string {
		return $this->server;
	}

	private final function setServer($server): void {
		$this->server = $server;
	}

	public final function getName(): string {
		return $this->name;
	}

	private final function setName($name): void {
		$this->name = $name;
	}

	public final function getUser(): string {
		return $this->user;
	}

	private final function setUser($user): void {
		$this->user = $user;
	}

	public final function getPassword(): string {
		return $this->password;
	}

	private final function setPassword($password): void {
		$this->password = $password;
	}

	public final function getLoggingLevel(): int {
		return $this->loggingLevel;
	}

	public function setLoggingLevel(int $loggingLevel, string $logTableName = '__log'): self {
		$this->loggingLevel = $loggingLevel;

		if($loggingLevel > 0) {
			$this->setLogTableName($logTableName);
		} else {
			unset($this->logTableName);
		}

		return $this;
	}

	public final function getLogTableName(): string {
		return $this->logTableName;
	}

	private final function setLogTableName(string $logTableName): void {
		$this->logTableName = $logTableName;
	}

}

