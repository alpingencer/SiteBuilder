<?php

namespace SiteBuilder\PageElement;

class StaticHTMLElement extends PageElement {
	private $html;

	public function __construct(string $html) {
		parent::__construct(array());
		$this->html = $html;
	}

	public static function newInstance(string $html): self {
		return new self($html);
	}

	public function getContent(): string {
		return $this->html;
	}

}
