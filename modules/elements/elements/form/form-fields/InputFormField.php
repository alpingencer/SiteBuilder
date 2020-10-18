<?php

namespace SiteBuilder\Elements;

class InputFormField extends FormField {
	private $type;
	private $attributes;

	public static function newInstance(string $formFieldName, string $column, string $type, string $defaultValue): self {
		return new self($formFieldName, $column, $type, $defaultValue);
	}

	public function __construct(string $formFieldName, string $column, string $type, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->setType($type);
		$this->clearAttributes();
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		$formFieldName = $this->getFormFieldName();
		$autoFocus = ($this->isAutoFocus()) ? ' autofocus' : '';
		$disabled = ($this->isDisabled()) ? ' disabled' : '';
		$required = ($this->isRequired()) ? ' required' : '';
		$attributes = (empty($this->attributes)) ? '' : ' ' . $this->attributes;
		$value = $this->prefill();
		$html = '<input type="' . $this->type . '" name="' . $formFieldName . '" value="' . $value . '"' . $attributes . $autoFocus . $disabled . $required . '>';
		return $html;
	}

	public function setType(string $type): self {
		$this->type = $type;
		return $this;
	}

	public function getType(): string {
		return $this->type;
	}

	public function setAttributes(string $attributes): self {
		$this->attributes = $attributes;
		return $this;
	}

	public function clearAttributes(): self {
		$this->setAttributes('');
		return $this;
	}

	public function getAttributes(): string {
		return $this->attributes;
	}

}
