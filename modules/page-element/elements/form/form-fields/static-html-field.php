<?php

namespace SiteBuilder\PageElement;

class StaticHTMLField extends FormField {
	private $innerHTML;

	public static function newInstance(string $innerHTML): self {
		return new self($innerHTML);
	}

	public function __construct(string $innerHTML) {
		$this->innerHTML = $innerHTML;
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		return $this->innerHTML;
	}

}
