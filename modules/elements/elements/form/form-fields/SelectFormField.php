<?php

namespace SiteBuilder\Elements;

class SelectFormField extends FormField {
	private $isMultiple;
	private $size;
	private $options;

	public static function newInstance(string $formFieldName, string $column): self {
		return new self($formFieldName, $column);
	}

	public function __construct(string $formFieldName, string $column) {
		parent::__construct($formFieldName, $column, '');
		$this->setMultiple(false);
		$this->clearSize();
		$this->clearOptions();
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		$formFieldName = $this->getFormFieldName();
		$autoFocus = ($this->isAutoFocus()) ? ' autofocus' : '';
		$disabled = ($this->isDisabled()) ? ' disabled' : '';
		$required = ($this->isRequired()) ? ' required' : '';
		$multiple = ($this->isMultiple) ? ' multiple' : '';
		$size = ($this->size === 0) ? '' : ' size="' . $this->size . '"';

		$html = '<select name="' . $formFieldName . '"' . $size . $multiple . $autoFocus . $disabled . $required . '>';

		foreach($this->options as $option) {
			$selected = ($option['selected']) ? ' selected' : '';
			$html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['prompt'] . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	public function prefill(): string {
		return '';
	}

	public function addOption(string $prompt, string $value, bool $selected = false): self {
		array_push($this->options, array(
				"prompt" => $prompt,
				"value" => $value,
				"selected" => $selected
		));

		return $this;
	}

	public function setMultiple(bool $isMultiple): self {
		$this->isMultiple = $isMultiple;
		return $this;
	}

	public function isMultiple(): bool {
		return $this->isMultiple;
	}

	public function setSize(int $size): self {
		$this->size = $size;
		return $this;
	}

	public function clearSize(): self {
		$this->setSize(0);
		return $this;
	}

	public function getSize(): int {
		return $this->size;
	}

	public function clearOptions(): self {
		$this->options = array();
		return $this;
	}

	public function getOptions(): array {
		return $this->options;
	}

}
