<?php

namespace SiteBuilder\Elements;

abstract class SortableTableElement extends Element {
	private $rows;

	public function __construct() {
		parent::__construct();
		$this->clearRows();
	}

	public function getDependencies(): array {
		return array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/sortable-table/sortable-table.js', 'defer'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/sortable-table/sortable-table.css')
		);
	}

	public function getContent(): string {
		// Set table id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set table classes
		$classes = 'sitebuilder-sortable-table';
		if(!empty($this->getHTMLClasses())) {
			$classes .= ' ' . $this->getHTMLClasses();
		}

		// Generate <thead>
		$html = '<table' . $id . ' class="' . $classes . '"><thead><tr>';

		foreach($this->getColumns() as $column) {
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

	protected abstract function getColumns(): array;

	protected function addRow(SortableTableRow $row): self {
		array_push($this->rows, $row);
		return $this;
	}

	protected function setRows(array $rows): self {
		$this->rows = $rows;
		return $this;
	}

	protected function clearRows(): self {
		$this->rows = array();
		return $this;
	}

}
