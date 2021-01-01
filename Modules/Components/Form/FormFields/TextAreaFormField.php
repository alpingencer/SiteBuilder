<?php

namespace SiteBuilder\Modules\Components\Form\FormFields;

use SiteBuilder\Modules\Components\Form\FormField;

class TextAreaFormField extends FormField {

	public static function init(string $formFieldName, string $column, string $defaultValue): TextAreaFormField {
		return new self($formFieldName, $column, $defaultValue);
	}

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
	}

	public function getContent(string $prefillValue, bool $isReadOnly, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<textarea name="' . $name . '"' . $disabled . $attributes . '>' . $prefillValue . '</textarea>';
		return $html;
	}

}

