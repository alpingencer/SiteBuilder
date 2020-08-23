<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\SiteBuilderComponent;

abstract class PageElement extends SiteBuilderComponent {
	private $dependencies;
	private $priority;

	public function __construct(array $dependencies) {
		$this->dependencies = $dependencies;
		$this->priority = 0;
	}

	public function getDependencies(): array {
		return $this->dependencies;
	}

	public function setPriority(int $priority): self {
		$this->priority = $priority;
		return $this;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	public abstract function getContent(): string;

}
