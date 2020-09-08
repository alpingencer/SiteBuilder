<?php

namespace SiteBuilder\PageElement;

class SortableTableElement extends PageElement {
	private $tableID, $tableClasses;
	private $columns, $rows;

	public static function newInstance(array $columns): self {
		return new self($columns);
	}

	public function __construct(array $columns) {
		parent::__construct();
		$this->columns = $columns;
		$this->rows = array();
		$this->tableID = '';
		$this->tableClasses = '';
	}

	public function getDependencies(): array {
		return array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/sortable-table/sortable-table.js', 'defer'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/sortable-table/sortable-table.css')
		);
	}

	public function getContent(): string {
		// Set table id
		if(empty($this->tableID)) {
			$id = '';
		} else {
			$id = ' id="' . $this->tableID . '"';
		}

		// Set table classes
		$classes = 'sitebuilder-sortable-table';
		if(!empty($this->tableClasses)) {
			$classes .= ' ' . $this->tableClasses;
		}

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

	public function setTableClasses(string $tableClasses): self {
		$this->tableClasses = $tableClasses;
		return $this;
	}

	public function getTableClasses(): array {
		return $this->tableClasses;
	}

}
