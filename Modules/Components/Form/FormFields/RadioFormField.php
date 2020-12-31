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

	public function getContent(string $prefillValue, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$attributes = $this->getHTMLAttributesAsString();

		$html = '';

		foreach($this->buttons as $button) {
			$checked = ($prefillValue === $button['value']) ? ' checked' : '';
			$html .= '<input type="radio" name="' . $name . '" value="' . $button['value'] . '"' . $checked . $attributes . '>';
			$html .= '<span>' . $button['prompt'] . '</span>';
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

