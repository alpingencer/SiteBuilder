<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\Elements\Element;

class AuthenticationElement extends Element {
	private $html;
	private $dependencies;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		parent::__construct();
		$this->clearHTML();
		$this->clearDependencies();
	}

	public function getDependencies(): array {
		return $this->dependencies;
	}

	public function getContent(): string {
		return $this->html;
	}

	public function setHTML(string $html): self {
		$this->html = $html;
		return $this;
	}

	public function clearHTML(): self {
		$this->setHTML('');
		return $this;
	}

	public function setDependencies(array $dependencies): self {
		$this->dependencies = $dependencies;
		return $this;
	}

	public function clearDependencies(): self {
		$this->setDependencies(array());
		return $this;
	}

}
