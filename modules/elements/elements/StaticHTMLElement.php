<?php

namespace SiteBuilder\Elements;

class StaticHTMLElement extends Element {
	private $html;

	public static function newInstance(string $html): self {
		return new self($html);
	}

	public function __construct(string $html) {
		$this->setHTML($html);
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		return $this->html;
	}

	public function setHTMLID(string $htmlID): self {
		trigger_error("Setting the HTML ID field has no effect on a StaticHTMLElement.", E_USER_NOTICE);
		return $this;
	}

	public function setHTMLClasses(string $htmlClasses): self {
		trigger_error("Setting the HTML Class field has no effect on a StaticHTMLElement.", E_USER_NOTICE);
		return $this;
	}

	public function addHTMLClasses(string $htmlClasses): self {
		trigger_error("Adding HTML Classes has no effect on a StaticHTMLElement.", E_USER_NOTICE);
		return $this;
	}

	public function setHTML(string $html): self {
		$this->html = $html;
		return $this;
	}

	public function clearHTML(): self {
		$this->setHTML('');
		return $this;
	}

	public function getHTML(): string {
		return $this->html;
	}

}
