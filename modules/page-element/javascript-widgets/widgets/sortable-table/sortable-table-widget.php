<?php

namespace SiteBuilder\PageElement;

class SortableTableWidget extends JavascriptWidget {
	private $tableID, $tableClasses;
	private $columns, $rows;

	public function __construct(array $columns) {
		$dependencies = array(
				new Dependency(SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/widgets/sortable-table/sortable-table.js', 'defer'),
				new Dependency(SITEBUILDER_CSS_DEPENDENCY, 'javascript-widgets/widgets/sortable-table/sortable-table.css')
		);
		parent::__construct($dependencies);
		$this->columns = $columns;
		$this->rows = array();
		$this->tableID = '';
		$this->tableClasses = array();
	}

	public static function newInstance(array $columns): self {
		return new self($columns);
	}

	public function addRow(SortableTableRow $row): self {
		array_push($this->rows, $row);
		return $this;
	}

	public function setRows(array $rows): self {
		$this->rows = $rows;
		return $this;
	}

	public function setTableID(string $tableID): self {
		$this->tableID = $tableID;
		return $this;
	}

	public function getTableID(): string {
		return $this->tableID;
	}

	public function setTableClasses(array $tableClasses): self {
		$this->tableClasses = $tableClasses;
		return $this;
	}

	public function getTableClasses(): array {
		return $this->tableClasses;
	}

	public function getContent(): string {
		// Set table id
		if(empty($this->tableID)) {
			$id = '';
		} else {
			$id = ' id="' . $this->tableID . '"';
		}

		// Set table classes
		$classes = 'sitebuilder-sortable-table ' . implode(' ', $this->tableClasses);

		// Generate <thead>
		$html = '<table' . $id . ' class="' . $classes . '"><thead><tr>';

		foreach($this->columns as $column) {
			$html .= '<th><a href="javascript:void(0);">' . $column . '</a></th>';
		}

		$html .= '</tr></thead>';

		// Generate <tbody>
		$html .= '<tbody>';

		foreach($this->rows as $row) {
			// Set onclick attribute
			if(empty(($row->getOnClick()))) {
				$html .= '<tr>';
			} else {
				$html .= '<tr onclick="' . $row->getOnClick() . '">';
			}

			// Add cells
			foreach($row->getCells() as $cell) {
				$html .= '<td>' . $cell->getInnerHTML() . '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		// Return result
		return $html;
	}

}

class SortableTableRow {
	private $onClick;
	private $cells;

	public function __construct() {
		$this->onClick = '';
		$this->cells = array();
	}

	public static function newInstance(): self {
		return new self();
	}

	public function setOnClick(string $onClick): self {
		$this->onClick = $onClick;
		return $this;
	}

	public function getOnClick(): string {
		return $this->onClick;
	}

	public function addCell(SortableTableCell $cell): self {
		array_push($this->cells, $cell);
		return $this;
	}

	public function setCells(array $cells): self {
		$this->cells = $cells;
		return $this;
	}

	public function getCells(): array {
		return $this->cells;
	}

}

class SortableTableCell {
	private $innerHTML;

	public function __construct(string $innerHTML) {
		$this->innerHTML = $innerHTML;
	}

	public function newInstance(string $innerHTML): self {
		return new self($innerHTML);
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
