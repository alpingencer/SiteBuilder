<?php

namespace SiteBuilder\PageElement;

class SortableTableCell {
	private $innerHTML;

	public function newInstance(string $innerHTML): self {
		return new self($innerHTML);
	}

	public function __construct(string $innerHTML) {
		$this->innerHTML = $innerHTML;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
