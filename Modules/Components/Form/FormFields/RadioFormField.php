<?php

namespace SiteBuilder\Modules\Components\Form\FormFields;

use SiteBuilder\Modules\Components\Form\FormField;

class RadioFormField extends FormField {
	private $buttons;

	public static function init(string $formFieldName, string $column, string $defaultValue = ''): RadioFormField {
		return new self($formFieldName, $column, $defaultValue);
	}

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->clearButtons();
	}

	public function getContent(string $prefillValue, bool $isReadOnly, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '';

		foreach($this->buttons as $button) {
			$checked = ($prefillValue === $button['value']) ? ' checked' : '';
			$html .= '<input type="radio" id="' . $name . '_' . $button['value'] . '" name="' . $name . '" value="' . $button['value'] . '"' . $checked . $disabled . $attributes . '>';
			$html .= '<label for="' . $name . '_' . $button['value'] . '">' . $button['prompt'] . '</label>';
		}
		return $html;
	}

	public function getButtons(): array {
		return $this->buttons;
	}

	public function addButton(string $value, string $prompt): self {
		array_push($this->buttons, array(
				'value' => $value,
				'prompt' => $prompt
		));
		return $this;
	}

	public function clearButtons(): self {
		$this->buttons = array();
		return $this;
	}

}

