<?php

namespace SiteBuilder\PageElement;

class ListElement extends PageElement {
	private $tableID, $tableDatabaseName;
	private $idColumnName, $idDatabaseName, $showID;
	private $columnNames, $columnDatabaseNames;
	private $sdefaultSort, $queryCriteria, $rowOnClickRef;

	public static function newInstance(string $tableDatabaseName): self {
		return new self($tableDatabaseName);
	}

	public function __construct(string $tableDatabaseName) {
		parent::__construct(array());
		$this->tableID = '';
		$this->tableDatabaseName = $tableDatabaseName;
		$this->idColumnName = 'ID';
		$this->idDatabaseName = 'ID';
		$this->showID = true;
		$this->columnNames = array();
		$this->columnDatabaseNames = array();
		$this->sdefaultSort = '';
		$this->queryCriteria = '1';
		$this->rowOnClickRef = '';
	}

	public function getContent(): string {
		return '';
	}

	public function addColumn(string $columnName, string $databaseName): self {
		array_push($this->columnNames, $columnName);
		array_push($this->columnDatabaseNames, $databaseName);
		return $this;
	}

	public function setTableID(string $tableID): self {
		$this->tableID = $tableID;
		return $this;
	}

	public function getTableID(): string {
		return $this->tableID;
	}

	public function getTableDatabaseName(): string {
		return $this->tableDatabaseName;
	}

	public function setIDColumnName(string $idColumnName): self {
		$this->idColumnName = $idColumnName;
		return $this;
	}

	public function getIDColumnName(): string {
		return $this->idColumnName;
	}

	public function setIDDatabaseName(string $idDatabaseName): self {
		$this->idDatabaseName = $idDatabaseName;
		return $this;
	}

	public function getIDDatabaseName(): string {
		return $this->idDatabaseName;
	}

	public function setShowID(bool $showID): self {
		$this->showID = $showID;
		return $this;
	}

	public function getShowID(): bool {
		return $this->showID;
	}

	public function getColumnNames(): array {
		return $this->columnNames;
	}

	public function getColumnDatabaseNames(): array {
		return $this->columnDatabaseNames;
	}

	public function setDefaultSort(string $sort): self {
		$this->sdefaultSort = $sort;
		return $this;
	}

	public function getDefaultSort(): string {
		if(empty($this->sdefaultSort)) {
			return $this->idDatabaseName;
		} else {
			return $this->sdefaultSort;
		}
	}

	public function setQueryCriteria(string $queryCriteria): self {
		$this->queryCriteria = $queryCriteria;
		return $this;
	}

	public function getQueryCriteria(): string {
		return $this->queryCriteria;
	}

	public function setRowOnClickRef(string $rowOnClickRef): self {
		$this->rowOnClickRef = $rowOnClickRef;
		return $this;
	}

	public function getRowOnClickRef(): string {
		return $this->rowOnClickRef;
	}

}
