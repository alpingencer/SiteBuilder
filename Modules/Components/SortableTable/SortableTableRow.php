<?php

namespace SiteBuilder\Modules\Components\SortableTable;

class SortableTableRow {
	private $cells;
	private $onClick;

	public static function init(string $onClick = ''): SortableTableRow {
		return new self($onClick);
	}

	private function __construct(string $onClick) {
		$this->setOnClick($onClick);
		$this->clearCells();
	}

	public function getCells(): array {
		return $this->cells;
	}

	public function addCell(string $innerHTML): self {
		array_push($this->cells, $innerHTML);
		return $this;
	}

	public function setCells($cells): self {
		$this->cells = $cells;
		return $this;
	}

	public function clearCells(): self {
		$this->cells = array();
		return $this;
	}

	public function getOnClick(): string {
		return $this->onClick;
	}

	private function setOnClick(string $onClick): void {
		$this->onClick = $onClick;
	}

}

