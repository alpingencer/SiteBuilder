<?php

namespace SiteBuilder\Modules\Components;

use SiteBuilder\Core\WM\PageHierarchy;
use SiteBuilder\Modules\Components\SortableTable\SortableTableComponent;
use SiteBuilder\Modules\Components\SortableTable\SortableTableRow;
use SiteBuilder\Modules\Database\DatabaseModule;

class ListComponent extends SortableTableComponent {
	private $tableDatabaseName;
	private $primaryColumnName;
	private $primaryKey;
	private $showPrimaryColumn;
	private $columnNames;
	private $columnKeys;
	private $defaultSort;
	private $queryCriteria;
	private $rowOnClickPath;

	public static function init(string $tableDatabaseName): SortableTableComponent {
		return new self($tableDatabaseName);
	}

	private function __construct(string $tableDatabaseName) {
		parent::__construct();
		$this->setTableDatabaseName($tableDatabaseName);
		$this->setPrimaryColumnName('ID');
		$this->setPrimaryKey('ID');
		$this->setShowPrimaryColumn(true);
		$this->clearColumns();
		$this->setDefaultSort('');
		$this->setQueryCriteria('1');
		$this->setRowOnClickPath('');
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Components\SortableTable\SortableTableComponent::getContent()
	 */
	public function getContent(): string {
		$mm = $GLOBALS['__SiteBuilder_ModuleManager'];

		// Query database
		$database = $mm->getModule(DatabaseModule::class)->db();
		$columns = $this->primaryKey . ', ' . implode(', ', $this->columnKeys);
		$result = $database->getRows($this->tableDatabaseName, $this->queryCriteria, $columns, $this->defaultSort);

		// Set rows
		foreach($result as $res) {
			// Set row onclick attribute
			if(empty($this->rowOnClickPath)) {
				$onClick = '';
			} else {
				$id = $res[$this->primaryKey];
				$onClick = "window.location.href='?p=$this->rowOnClickPath&amp;id=$id'";
			}

			// Set row
			$row = SortableTableRow::init($onClick);

			// If show primary column is true, add ID column
			if($this->showPrimaryColumn) {
				$row->addCell($res[$this->primaryKey]);
			}

			foreach($this->columnKeys as $columnKey) {
				$row->addCell($res[$columnKey]);
			}

			$this->addRow($row);
		}

		// Set classes
		$classes = 'sitebuilder-list-table ';
		if(!empty($this->rowOnClickPagePath)) {
			$classes .= 'sitebuilder-hover-table ';
		}
		$this->setHTMLClasses($classes . $this->getHTMLClasses());

		// Use SortableTableComponent::getContent() to generate complete HTML
		return parent::getContent();
	}

	protected function getColumns(): array {
		if($this->showPrimaryColumn) {
			return array_merge([
					$this->primaryColumnName
			], $this->columnNames);
		} else {
			return $this->columnNames;
		}
	}

	public function addColumn(string $key, string $name): self {
		array_push($this->columnKeys, $key);
		array_push($this->columnNames, $name);
		return $this;
	}

	private function clearColumns(): void {
		$this->columnKeys = array();
		$this->columnNames = array();
	}

	public function getTableDatabaseName(): string {
		return $this->tableDatabaseName;
	}

	private function setTableDatabaseName(string $tableDatabaseName): void {
		$this->tableDatabaseName = $tableDatabaseName;
	}

	public function getPrimaryColumnName(): string {
		return $this->primaryColumnName;
	}

	public function setPrimaryColumnName(string $primaryColumnName): self {
		$this->primaryColumnName = $primaryColumnName;
		return $this;
	}

	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	public function setPrimaryKey(string $primaryKey): self {
		$this->primaryKey = $primaryKey;
		return $this;
	}

	public function getShowPrimaryColumn(): bool {
		return $this->showPrimaryColumn;
	}

	public function setShowPrimaryColumn(bool $showPrimaryColumn): self {
		$this->showPrimaryColumn = $showPrimaryColumn;
		return $this;
	}

	public function getColumnNames(): array {
		return $this->columnNames;
	}

	public function getColumnKeys(): array {
		return $this->columnKeys;
	}

	public function getDefaultSort(): string {
		return $this->defaultSort;
	}

	public function setDefaultSort(string $defaultSort): self {
		$this->defaultSort = $defaultSort;
		return $this;
	}

	public function getQueryCriteria(): string {
		return $this->queryCriteria;
	}

	public function setQueryCriteria(string $queryCriteria): self {
		$this->queryCriteria = $queryCriteria;
		return $this;
	}

	public function getRowOnClickPath(): string {
		return $this->rowOnClickPath;
	}

	public function setRowOnClickPath(string $rowOnClickPath): self {
		$rowOnClickPath = PageHierarchy::normalizePathString($rowOnClickPath);
		$this->rowOnClickPath = $rowOnClickPath;
		return $this;
	}

}

