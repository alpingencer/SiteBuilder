<?php

namespace SiteBuilder\PageElement;

class StaticHTMLElement extends PageElement {
	private $html;

	public static function newInstance(string $html): self {
		return new self($html);
	}

	public function __construct(string $html) {
		parent::__construct();
		$this->html = $html;
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		return $this->html;
	}

}
