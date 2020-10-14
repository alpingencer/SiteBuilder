<?php

namespace SiteBuilder\Elements;

class InputBoxFormField extends FormField {

	public static function newInstance(string $formFieldName, string $column, string $defaultValue): self {
		return new self($formFieldName, $column, $defaultValue);
	}

	public function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		return '<input type="text" name="' . $this->getFormFieldName() . '" value="' . $this->prefill() . '">';
	}

}
