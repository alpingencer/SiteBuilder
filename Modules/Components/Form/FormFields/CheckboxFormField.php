<?php

namespace SiteBuilder\Modules\Components\Form\FormFields;

use SiteBuilder\Modules\Components\Form\FormField;

class CheckboxFormField extends FormField {
	private $prompt;

	public static function init(string $formFieldName, string $column, bool $defaultValue = false, string $prompt = ''): CheckboxFormField {
		return new self($formFieldName, $column, $defaultValue, $prompt);
	}

	protected function __construct(string $formFieldName, string $column, bool $defaultValue, string $prompt) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->setPrompt($prompt);
	}

	public function getContent(string $prefillValue, bool $isReadOnly, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$checked = ($prefillValue === 'YES') ? ' checked' : '';
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<input type="hidden" name="' . $name . '" value="NO">';
		$html .= '<input type="checkbox" id="' . $name . '" name="' . $name . '" value="YES"' . $checked . $disabled . $attributes . '>';

		if(!empty($this->prompt)) {
			$html .= '<label for="' . $name . '">' . $this->prompt . '</label>';
		}
		return $html;
	}

	public function getPrompt(): string {
		return $this->prompt;
	}

	private function setPrompt(string $prompt): void {
		$this->prompt = $prompt;
	}

}

