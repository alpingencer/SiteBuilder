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

	public function getContent(string $prefillValue, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<textarea name="' . $name . '"' . $attributes . '>' . $prefillValue . '</textarea>';
		return $html;
	}

}

