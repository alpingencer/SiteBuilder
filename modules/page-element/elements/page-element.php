<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\SiteBuilderComponent;

abstract class PageElement extends SiteBuilderComponent {
	private $priority;

	public function __construct() {
		$this->priority = 0;
	}

	public function setPriority(int $priority): self {
		$this->priority = $priority;
		return $this;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	public abstract function getDependencies(): array;

	public abstract function getContent(): string;

}
