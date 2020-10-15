<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Database\DatabaseComponent;
use ErrorException;
use function SiteBuilder\normalizePathString;

class ListElement extends SortableTableElement {
	private $tableDatabaseName;
	private $idColumnName;
	private $idDatabaseName;
	private $showID;
	private $columnNames;
	private $columnDatabaseNames;
	private $defaultSort;
	private $queryCriteria;
	private $rowOnClickPagePath;

	public static function newInstance(string $tableDatabaseName): self {
		return new self($tableDatabaseName);
	}

	public function __construct(string $tableDatabaseName) {
		parent::__construct();
		$this->setTableDatabaseName($tableDatabaseName);
		$this->setIDColumnName('ID');
		$this->setIDDatabaseName('ID');
		$this->setShowID(true);
		$this->clearColumns();
		$this->clearDefaultSort();
		$this->clearQueryCriteria();
		$this->clearRowOnClickPagePath();
	}

	public function getDependencies(): array {
		return parent::getDependencies();
	}

	public function getContent(): string {
		$sb = $GLOBALS['__SiteBuilder_Core'];

		if(!$sb->getCurrentPage()->hasComponentsByClass(DatabaseComponent::class)) {
			throw new ErrorException("No DatabaseComponent found when using a ListElement!");
		}

		// Query database
		$database = $sb->getCurrentPage()->getComponentByClass(DatabaseComponent::class);
		$columns = $this->idDatabaseName . ', ' . implode(', ', $this->columnDatabaseNames);
		$result = $database->getRows($this->tableDatabaseName, $this->queryCriteria, $columns, $this->defaultSort);

		// Set rows
		foreach($result as $res) {
			// Set row onclick attribute
			if(empty($this->rowOnClickPagePath)) {
				$onClick = '';
			} else {
				$id = $res[$this->idDatabaseName];
				$onClick = "window.location.href='?p=$this->rowOnClickPagePath&amp;id=$id'";
			}

			// Set cells
			$cells = array();

			// If show ID is true, add ID column
			if($this->showID) {
				array_push($cells, SortableTableCell::newInstance($res[$this->idDatabaseName]));
			}

			foreach($this->columnDatabaseNames as $columnDatabaseName) {
				array_push($cells, SortableTableCell::newInstance($res[$columnDatabaseName]));
			}

			$this->addRow(SortableTableRow::newInstance()->setOnClick($onClick)->setCells($cells));
		}

		// Set classes
		$classes = 'sitebuilder-list-table ';
		if(!empty($this->rowOnClickPagePath)) {
			$classes .= 'sitebuilder-hover-table ';
		}
		$this->setHTMLClasses($classes . $this->getHTMLClasses());

		// Use SortableTableElement::getContent() to generate complete HTML
		return parent::getContent();
	}

	protected function getColumns(): array {
		if($this->showID) {
			$columnNames = $this->getColumnNames();
			array_unshift($columnNames, $this->idColumnName);
			return $columnNames;
		} else {
			return $this->getColumnNames();
		}
	}

	public function addColumn(string $columnName, string $databaseName): self {
		array_push($this->columnNames, $columnName);
		array_push($this->columnDatabaseNames, $databaseName);
		return $this;
	}

	public function clearColumns(): self {
		$this->columnNames = array();
		$this->columnDatabaseNames = array();
		return $this;
	}

	public function setTableDatabaseName(string $tableDatabaseName): self {
		$this->tableDatabaseName = $tableDatabaseName;
		return $this;
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

	public function clearDefaultSort(): self {
		$this->setDefaultSort('1');
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

	public function clearQueryCriteria(): self {
		$this->setQueryCriteria('1');
		return $this;
	}

	public function getQueryCriteria(): string {
		return $this->queryCriteria;
	}

	public function setRowOnClickPagePath(string $rowOnClickPagePath): self {
		$this->rowOnClickPagePath = normalizePathString($rowOnClickPagePath);
		return $this;
	}

	public function clearRowOnClickPagePath(): self {
		$this->setRowOnClickPagePath('');
		return $this;
	}

	public function getRowOnClickPagePath(): string {
		return $this->rowOnClickPagePath;
	}

}
