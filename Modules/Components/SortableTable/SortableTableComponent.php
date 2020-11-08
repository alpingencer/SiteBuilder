<?php

namespace SiteBuilder\Modules\Components\SortableTable;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\CM\Dependency\CSSDependency;
use SiteBuilder\Core\CM\Dependency\JSDependency;

abstract class SortableTableComponent extends Component {
	private $rows;

	protected function __construct() {
		parent::__construct();
		$this->clearRows();
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		return array(
				JSDependency::init('SortableTable/sortable-table.js', 'defer'),
				CSSDependency::init('SortableTable/sortable-table.css')
		);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
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
				$html .= '<tr class="sitebuilder-sortable-table--hover-tr" onclick="' . $row->getOnClick() . '">';
			}

			// Add cells
			foreach($row->getCells() as $cell) {
				$html .= '<td>' . $cell . '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		// Return result
		return $html;
	}

	protected abstract function getColumns(): array;

	public final function getRows(): array {
		return $this->rows;
	}

	protected final function addRow(SortableTableRow $row): self {
		array_push($this->rows, $row);
		return $this;
	}

	protected final function setRows(array $rows): self {
		$this->rows = $rows;
		return $this;
	}

	protected final function clearRows(): self {
		$this->rows = array();
		return $this;
	}

}

