<?php
use SiteBuilder\SiteBuilderComponent;

class ExampleComponent extends SiteBuilderComponent {
	private $myStringField;
	private $myIntField;

	public function __construct(string $myStringField, int $myIntField) {
		$this->myStringField = $myStringField;
		$this->myIntField = $myIntField;
	}

	public static function newInstance(string $myStringField, int $myIntField): self {
		return new self($myStringField, $myIntField);
	}

	public function getMyStringField(): string {
		return $this->myStringField;
	}

	public function getMyIntField(): int {
		return $this->myIntField;
	}

}
