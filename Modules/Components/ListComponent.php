<?php

namespace SiteBuilder\Modules\Components;

use SiteBuilder\Core\WM\PageHierarchy;
use SiteBuilder\Modules\Components\SortableTable\SortableTableComponent;
use SiteBuilder\Modules\Components\SortableTable\SortableTableRow;
use SiteBuilder\Modules\Database\DatabaseModule;
use ErrorException;

/**
 * <p>
 * A List Component provides a frontend to displaying data from a database in a webpage.
 * As ListComponent extends SortableTableComponent, the generated table will also be sortable by
 * clicking the generated columns.
 * </p>
 * <p>
 * Please note that ListComponent currently only works with a MySQL database.
 * </p>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components;
 */
class ListComponent extends SortableTableComponent {
	/**
	 * The name of the database table to pull the data from
	 *
	 * @var string
	 */
	private $tableDatabaseName;
	/**
	 * The display name of the primary ID column to be output to the browser
	 *
	 * @var string
	 */
	private $primaryColumnName;
	/**
	 * The database name of the primary ID column in the database
	 *
	 * @var string
	 */
	private $primaryKey;
	/**
	 * Wether to generate HTML for the primary column
	 *
	 * @var bool
	 */
	private $showPrimaryColumn;
	/**
	 * An array of strings of the display names of the added columns
	 *
	 * @var array
	 */
	private $columnNames;
	/**
	 * An array of strings of the database names of the added columns in the database
	 *
	 * @var array
	 */
	private $columnKeys;
	/**
	 * Wether to generate HTML that outputs the total row count
	 *
	 * @var bool
	 */
	private $showRowCount;
	/**
	 * The database key to sort the data by by default
	 *
	 * @var string
	 */
	private $defaultSort;
	/**
	 * A string of additional criteria in the 'WHERE' part of the query to the database
	 *
	 * @var string
	 */
	private $queryCriteria;
	/**
	 * If specified, clicking on a row in the table will redirect to the given page path with an
	 * additional 'id' GET parameter corresponding to the primary ID key of the row
	 *
	 * @var string
	 */
	private $rowOnClickPath;

	/**
	 * Returns an instance of ListComponent
	 *
	 * @param string $tableDatabaseName The name of the database table to pull the data from
	 * @return ListComponent The initialized instance
	 */
	public static function init(string $tableDatabaseName): ListComponent {
		return new self($tableDatabaseName);
	}

	/**
	 * Constructor for the ListComponent.
	 * To get an instance of this class, use ListComponent::init()
	 *
	 * @param string $tableDatabaseName The name of the databsae table to pull the data from
	 * @see ListComponent The initialized instance
	 */
	private function __construct(string $tableDatabaseName) {
		parent::__construct();
		$this->setTableDatabaseName($tableDatabaseName);
		$this->clearPrimaryColumnName();
		$this->clearPrimaryKey();
		$this->setShowPrimaryColumn(true);
		$this->clearColumns();
		$this->setShowRowCount(false);
		$this->clearDefaultSort();
		$this->clearQueryCriteria();
		$this->clearRowOnClickPath();

		// Check if database module is initialized
		// If no, throw error: Cannot use ListComponent without DatabaseModule
		if(!$GLOBALS['__SiteBuilder_ModuleManager']->isModuleInitialized(DatabaseModule::class)) {
			throw new ErrorException("The DatabaseModule must be initialized when using a ListComponent!");
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Components\SortableTable\SortableTableComponent::getContent()
	 */
	public function getContent(): string {
		$mm = $GLOBALS['__SiteBuilder_ModuleManager'];

		// Query database
		$database = $mm->getModuleByClass(DatabaseModule::class)->db();
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
				$row->addCell($res[$columnKey] ?? '');
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
		$html = parent::getContent();

		// Generate show row count HTML
		if($this->showRowCount) {
			$rowCountHTML = '<p class="sitebuilder-list-row-count">Total # of rows: ' . sizeof($result) . '</p>';
			$html .= $rowCountHTML;
		}

		return $html;
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Modules\Components\SortableTable\SortableTableComponent::getColumns()
	 */
	protected function getColumns(): array {
		if($this->showPrimaryColumn) {
			return array_merge([
					$this->primaryColumnName
			], $this->columnNames);
		} else {
			return $this->columnNames;
		}
	}

	/**
	 * Add a column to the ListComponent
	 *
	 * @param string $key The column's name in the database
	 * @param string $name The column's display name
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$columnKeys
	 * @see ListComponent::$columnNames
	 */
	public function addColumn(string $name, string $key): self {
		array_push($this->columnKeys, $key);
		array_push($this->columnNames, $name);
		return $this;
	}

	/**
	 * Clears a previously added column
	 *
	 * @see ListComponent::$columnKeys
	 * @see ListComponent::$columnNames
	 */
	private function clearColumns(): void {
		$this->columnKeys = array();
		$this->columnNames = array();
	}

	/**
	 * Getter for the table database name
	 *
	 * @return string
	 * @see ListComponent::$tableDatabaseName
	 */
	public function getTableDatabaseName(): string {
		return $this->tableDatabaseName;
	}

	/**
	 * Setter for the table database name
	 *
	 * @param string $tableDatabaseName
	 * @see ListComponent::$tableDatabaseName
	 */
	private function setTableDatabaseName(string $tableDatabaseName): void {
		$this->tableDatabaseName = $tableDatabaseName;
	}

	/**
	 * Getter for the primary column display name
	 *
	 * @return string
	 * @see ListComponent::$primaryColumnName
	 */
	public function getPrimaryColumnName(): string {
		return $this->primaryColumnName;
	}

	/**
	 * Setter for the primary column display name
	 *
	 * @param string $primaryColumnName
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$primaryColumnName
	 */
	public function setPrimaryColumnName(string $primaryColumnName): self {
		$this->primaryColumnName = $primaryColumnName;
		return $this;
	}

	/**
	 * Resets the primary column display name to the SiteBuilder default value 'ID'
	 *
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$primaryColumnName
	 */
	public function clearPrimaryColumnName(): self {
		$this->setPrimaryColumnName('ID');
		return $this;
	}

	/**
	 * Getter for the primary database key
	 *
	 * @return string
	 * @see ListComponent::$primaryKey
	 */
	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	/**
	 * Setter for the primary database key
	 *
	 * @param string $primaryKey
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$primaryKey
	 */
	public function setPrimaryKey(string $primaryKey): self {
		$this->primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * Resets the primary database key to the SiteBuilder default value 'ID';
	 *
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$primaryKey
	 */
	public function clearPrimaryKey(): self {
		$this->setPrimaryKey('ID');
		return $this;
	}

	/**
	 * Getter for wether to show the primary column
	 *
	 * @return bool
	 * @see ListComponent::$showPrimaryColumn
	 */
	public function getShowPrimaryColumn(): bool {
		return $this->showPrimaryColumn;
	}

	/**
	 * Setter for wether to show the primary column
	 *
	 * @param bool $showPrimaryColumn
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$showPrimaryColumn
	 */
	public function setShowPrimaryColumn(bool $showPrimaryColumn): self {
		$this->showPrimaryColumn = $showPrimaryColumn;
		return $this;
	}

	/**
	 * Getter for the added column names
	 *
	 * @return array
	 * @see ListComponent::$columnNames
	 */
	public function getColumnNames(): array {
		return $this->columnNames;
	}

	/**
	 * Getter for the added column keys
	 *
	 * @return array
	 * @see ListComponent::$columnKeys
	 */
	public function getColumnKeys(): array {
		return $this->columnKeys;
	}

	/**
	 * Getter for wether to show the row count
	 *
	 * @return bool
	 * @see ListComponent::$showRowCount
	 */
	public function getShowRowCount(): bool {
		return $this->showRowCount;
	}

	/**
	 * Setter for wether to show the row count
	 *
	 * @param bool $showRowCount
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$showRowCount
	 */
	public function setShowRowCount(bool $showRowCount): self {
		$this->showRowCount = $showRowCount;
		return $this;
	}

	/**
	 * Getter for the column to sort by by default
	 *
	 * @return string
	 * @see ListComponent::$defaultSort
	 */
	public function getDefaultSort(): string {
		return $this->defaultSort;
	}

	/**
	 * Setter for the column to sort by by default
	 *
	 * @param string $defaultSort
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$defaultSort
	 */
	public function setDefaultSort(string $defaultSort): self {
		$this->defaultSort = $defaultSort;
		return $this;
	}

	/**
	 * Clears the previously defined column to sort by by default
	 *
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$defaultSort
	 */
	public function clearDefaultSort(): self {
		$this->setDefaultSort('');
		return $this;
	}

	/**
	 * Getter for the additional MySQL 'WHERE' criteria
	 *
	 * @return string
	 * @see ListComponent::$queryCriteria
	 */
	public function getQueryCriteria(): string {
		return $this->queryCriteria;
	}

	/**
	 * Setter for the additional MySQL 'WHERE' criteria
	 *
	 * @param string $queryCriteria
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$queryCriteria
	 */
	public function setQueryCriteria(string $queryCriteria): self {
		$this->queryCriteria = $queryCriteria;
		return $this;
	}

	/**
	 * Clears the previously defined additional MySQL 'WHERE' criteria
	 *
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$queryCriteria
	 */
	public function clearQueryCriteria(): self {
		$this->setQueryCriteria('1');
		return $this;
	}

	/**
	 * Getter for the page path to redirect to when a row has been clicked
	 *
	 * @return string
	 * @see ListComponent::$rowOnClickPath
	 */
	public function getRowOnClickPath(): string {
		return $this->rowOnClickPath;
	}

	/**
	 * Setter for the page path to redirect to when a row has been clicked
	 *
	 * @param string $rowOnClickPath
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$rowOnClickPath
	 */
	public function setRowOnClickPath(string $rowOnClickPath): self {
		$rowOnClickPath = PageHierarchy::normalizePathString($rowOnClickPath);
		$this->rowOnClickPath = $rowOnClickPath;
		return $this;
	}

	/**
	 * Returns the previously defined page path to redirect to when a row has been clicked
	 *
	 * @return self Returns itself for chaining other functions
	 * @see ListComponent::$rowOnClickPath
	 */
	public function clearRowOnClickPath(): self {
		$this->setRowOnClickPath('');
		return $this;
	}

}

