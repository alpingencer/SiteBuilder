<?php

namespace SiteBuilder\Modules\Components\SortableTable;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\CM\Dependency\CSSDependency;
use SiteBuilder\Core\CM\Dependency\JSDependency;
use SiteBuilder\Modules\Components\ListComponent;

/**
 * <p>
 * The SortableTableComponent class provides a base class for componenets that need to display data
 * in an HTML table that is sortable by clicking on the column names.
 * </p>
 * <p>
 * To get started, define the getColumns() abstract method and add rows to the table using addRow(),
 * setRows() and clearRows(). For an example implementation, see ListComponent.
 * </p>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components\SortableTable
 * @see SortableTableComponent::addRow()
 * @see SortableTableComponent::setRows()
 * @see SortableTableComponent::clearRows()
 * @see ListComponent
 */
abstract class SortableTableComponent extends Component {
	/**
	 * An array of SortableTableRows added to this table
	 *
	 * @var array
	 */
	private $rows;

	/**
	 * Constructor for the SortableTableComponent.
	 * Please note that when extending this abstract class, it is convention to set the visibility
	 * of the constructor to private and create a public static init() method. See
	 * ListComponent for an example.
	 *
	 * @see ListComponent
	 */
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

	/**
	 * This method should return an array of strings containing the names of the columns.
	 *
	 * @return array
	 */
	protected abstract function getColumns(): array;

	/**
	 * Getter for the rows
	 *
	 * @return array
	 * @see SortableTableComponent::$rows
	 */
	public final function getRows(): array {
		return $this->rows;
	}

	/**
	 * Adds a row to the table
	 *
	 * @param SortableTableRow $row The row to add
	 * @return self Returns itself for chaining other functions.
	 * @see SortableTableComponent::$rows
	 */
	protected final function addRow(SortableTableRow $row): self {
		array_push($this->rows, $row);
		return $this;
	}

	/**
	 * Sets the rows field to a given array
	 *
	 * @param array $rows An array of SortableTableRows
	 * @return self Returns itself for chaining other functions
	 * @see SortableTableComponent::$rows
	 */
	protected final function setRows(array $rows): self {
		$this->rows = $rows;
		return $this;
	}

	/**
	 * Clears all previously added rows
	 *
	 * @return self Returns itself for chaining other functions
	 * @see SortableTableComponent::$rows
	 */
	protected final function clearRows(): self {
		$this->rows = array();
		return $this;
	}

}

