<?php

namespace SiteBuilder\Modules\Components\SortableTable;

/**
 * The SortableTableRow class is a data structure used by SortableTableComponent.
 * It is responsible for storing the individual cells of the table and the 'onclick' HTML attribute
 * of the corresponding <tr>.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components\SortableTable
 * @see SortableTableComponent
 */
class SortableTableRow {
	/**
	 * An array storing the inner HTML strings of the cells in this row
	 *
	 * @var array
	 */
	private $cells;
	/**
	 * The 'onclick' HTML attribute of the <tr> tag to be generated
	 *
	 * @var string
	 */
	private $onClick;

	/**
	 * Returns an instance of SortableTableRow
	 *
	 * @param string $onClick The 'onclick' HTML attribute of the <tr> tag to be generated
	 * @return SortableTableRow The initialized instance
	 */
	public static function init(string $onClick = ''): SortableTableRow {
		return new self($onClick);
	}

	/**
	 * Constructor for the SortableTableRow.
	 * To get an instance of this class, use SortableTableRow::init()
	 *
	 * @param string $onClick The 'onclick' HTML attribute of the <tr> tag to be generated
	 * @see SortableTableRow::init()
	 */
	protected function __construct(string $onClick) {
		$this->setOnClick($onClick);
		$this->clearCells();
	}

	/**
	 * Getter for the cells
	 *
	 * @return array
	 * @see SortableTableRow::$cells
	 */
	public function getCells(): array {
		return $this->cells;
	}

	/**
	 * Adds a cell to this row
	 *
	 * @param string $innerHTML The inner HTML of the <td> tag to be generated
	 * @return self Returns itself for chaining other functions
	 * @see SortableTableRow::$rows
	 */
	public function addCell(string $innerHTML): self {
		array_push($this->cells, $innerHTML);
		return $this;
	}

	/**
	 * Sets the cell field to a given array
	 *
	 * @param array $cells An array storing the inner HTML strings of the cells in this row
	 * @return self Returns itself for chaining other functions
	 * @see SortableTableRow::$cells
	 */
	public function setCells(array $cells): self {
		$this->cells = $cells;
		return $this;
	}

	/**
	 * Clears all previously added cells
	 *
	 * @return self Returns itself for chaining other functions
	 * @see SortableTableRow::$cells
	 */
	public function clearCells(): self {
		$this->cells = array();
		return $this;
	}

	/**
	 * Getter for the HTML 'onclick' attribute
	 *
	 * @return string
	 * @see SortableTableRow::$onClick
	 */
	public function getOnClick(): string {
		return $this->onClick;
	}

	/**
	 * Setter for the HTML 'onclick' attribute
	 *
	 * @param string $onClick
	 * @see SortableTableRow::$onClick
	 */
	private function setOnClick(string $onClick): void {
		$this->onClick = $onClick;
	}

}

