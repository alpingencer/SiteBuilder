<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\Database\DatabaseComponent;
use ErrorException;

class ListElement extends PageElement {
	private $tableID, $tableDatabaseName;
	private $idColumnName, $idDatabaseName, $showID;
	private $columnNames, $columnDatabaseNames;
	private $defaultSort, $queryCriteria, $rowOnClickRef;

	public static function newInstance(string $tableDatabaseName): self {
		return new self($tableDatabaseName);
	}

	public function __construct(string $tableDatabaseName) {
		parent::__construct();
		$this->tableID = '';
		$this->tableDatabaseName = $tableDatabaseName;
		$this->idColumnName = 'ID';
		$this->idDatabaseName = 'ID';
		$this->showID = true;
		$this->columnNames = array();
		$this->columnDatabaseNames = array();
		$this->defaultSort = '1';
		$this->queryCriteria = '1';
		$this->rowOnClickRef = '';
	}

	public function getDependencies(): array {
		return SortableTableElement::getDependencies();
	}

	public function getContent(): string {
		$database = $GLOBALS['__SiteBuilderCore']->getCurrentPage()->getComponent(DatabaseComponent::class);

		if(is_null($database)) {
			throw new ErrorException('No DatabaseComponent found when using a ListElement!');
		}

		// Query database
// 		$query = 'SELECT ' . $this->idDatabaseName . ', ' . implode(', ', $this->columnDatabaseNames);
// 		$query .= ' FROM ' . $this->tableDatabaseName;
// 		$query .= ' WHERE ' . $this->queryCriteria;
// 		$query .= ' ORDER BY ' . $this->defaultSort;
// 		$result = $database->query($query);
		$columns = $this->idDatabaseName . ', ' . implode(', ', $this->columnDatabaseNames);
		$result = $database->getRows($this->tableDatabaseName, $this->queryCriteria, $columns, $this->defaultSort);

		// Set table id
		$tableID = $this->tableID;

		// Set table classes
		$tableClasses = 'sitebuilder-list-table';
		if(!empty($this->rowOnClickRef)) {
			$tableClasses .= ' sitebuilder-hover-table';
		}

		// Set columns
		$columns = array();
		for($i = 0; $i < count($this->columnNames) + 1; $i++) {
			// If not show id, skip
			if($i === 0 && !$this->showID) continue;

			if($i === 0) {
				$column = $this->idColumnName;
			} else {
				$column = $this->columnNames[$i - 1];
			}

			array_push($columns, $column);
		}

		// Set rows
		$rows = array();
		foreach($result as $res) {
			// Set row onclick attribute
			if(empty($this->rowOnClickRef)) {
				$onClick = '';
			} else {
				$id = $res[$this->idDatabaseName];
				// Check if on click ref has other get parameters
				if(strpos($this->rowOnClickRef, '?') !== false) {
					$onClick = 'window.location.href=\'' . $this->rowOnClickRef . '&amp;id=' . $id . '\'';
				} else {
					$onClick = 'window.location.href=\'' . $this->rowOnClickRef . '?id=' . $id . '\'';
				}
			}

			// Set cells
			$cells = array();
			foreach($this->columnDatabaseNames as $columndatabaseName) {
				// If not show id, skip
				if(!$this->showID && $columndatabaseName === $this->idDatabaseName) continue;

				$cell = SortableTableCell::newInstance($res[$columndatabaseName]);
				array_push($cells, $cell);
			}

			$row = SortableTableRow::newInstance()->setOnClick($onClick)->setCells($cells);
			array_push($rows, $row);
		}

		// Create a SortableTableElement to generate the HTML for the ListElement.
		$sortableTable = SortableTableElement::newInstance($columns)->setPriority($this->getPriority())->setTableID($tableID)->setTableClasses($tableClasses)->setRows($rows);
		return $sortableTable->getContent();
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
		$this->defaultSort = $sort;
		return $this;
	}

	public function getDefaultSort(): string {
		if(empty($this->defaultSort)) {
			return $this->idDatabaseName;
		} else {
			return $this->defaultSort;
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
