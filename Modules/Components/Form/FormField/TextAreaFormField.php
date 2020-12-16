<?php

namespace SiteBuilder\Modules\Components\Form\FormField;

class TextAreaFormField extends FormField {

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
	}

	public function getDependencies(): array {}

	public function getContent(string $prefillValue, string $formFieldNameSuffix = ''): string {}

}

