<?php

namespace SiteBuilder\Modules\Components\Form\FormField;

class DateFormField extends FormField {

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
	}

	public function getDependencies(): array {}

	public function getContent(string $prefillValue, string $formFieldNameSuffix = ''): string {}

}

