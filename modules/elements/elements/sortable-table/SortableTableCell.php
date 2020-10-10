<?php

namespace SiteBuilder\Elements;

class SortableTableCell {
	private $innerHTML;

	public function newInstance(string $innerHTML): self {
		return new self($innerHTML);
	}

	public function __construct(string $innerHTML) {
		$this->setInnerHTML($innerHTML);
	}

	public function setInnerHTML($innerHTML): self {
		$this->innerHTML = $innerHTML;
		return $this;
	}

	public function clearInnerHTML(): self {
		$this->setInnerHTML('');
		return $this;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
