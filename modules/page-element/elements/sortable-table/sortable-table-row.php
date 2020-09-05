<?php

namespace SiteBuilder\PageElement;

class SortableTableRow {
	private $onClick;
	private $cells;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$this->onClick = '';
		$this->cells = array();
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
