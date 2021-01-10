<?php

namespace SiteBuilder\Modules\Components\Form\FormFields;

use SiteBuilder\Modules\Components\Form\FormField;

class InputFormField extends FormField {
	private $type;

	public static function init(string $formFieldName, string $column, string $defaultValue, string $type): InputFormField {
		return new self($formFieldName, $column, $defaultValue, $type);
	}

	protected function __construct(string $formFieldName, string $column, string $defaultValue, string $type) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->setType($type);
	}

	public function getContent(string $prefillValue, bool $isReadOnly, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<input type="' . $this->type . '" name="' . $name . '" value="' . $prefillValue . '"' . $disabled . $attributes . '>';
		return $html;
	}

	public function getType(): string {
		return $this->type;
	}

	private function setType(string $type): void {
		$this->type = $type;
	}

}

