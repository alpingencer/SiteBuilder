<?php

namespace SiteBuilder\Elements;

class StaticHTMLElement extends Element {
	private $html;

	public static function newInstance(string $html): self {
		return new self($html);
	}

	public function __construct(string $html) {
		parent::__construct();
		$this->setHTML($html);
	}

	public function getDependencies(): array {
		return array();
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

}
