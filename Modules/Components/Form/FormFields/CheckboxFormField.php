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
		$checked = ($prefillValue === '1') ? ' checked' : '';
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<input type="hidden" name="' . $name . '" value="0">';
		$html .= '<input type="checkbox" name="' . $name . '" value="1"' . $checked . $disabled . $attributes . '>';

		if(!empty($this->prompt)) {
			$html .= '<span>' . $this->prompt . '</span>';
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

