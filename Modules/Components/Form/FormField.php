<?php

namespace SiteBuilder\Modules\Components\Form;

abstract class FormField {
	private $formFieldName;
	private $column;
	private $defaultValue;
	private $htmlAttributes;
	private $parentFieldset;

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		$this->setFormFieldName($formFieldName);
		$this->setColumn($column);
		$this->setDefaultValue($defaultValue);
		$this->clearHTMLAttributes();
	}

	public function getDependencies(): array {
		return array();
	}

	public abstract function getContent(string $prefillValue, string $formFieldNameSuffix = ''): string;

	public final function getFormFieldName(): string {
		return $this->formFieldName;
	}

	private final function setFormFieldName(string $formFieldName): void {
		$this->formFieldName = $formFieldName;
	}

	public final function getColumn(): string {
		return $this->column;
	}

	private final function setColumn(string $column): void {
		$this->column = $column;
	}

	public final function getDefaultValue(): string {
		return $this->defaultValue;
	}

	private final function setDefaultValue(string $defaultValue): void {
		$this->defaultValue = $defaultValue;
	}

	public function getHTMLAttribute(string $attribute): array {
		return $this->htmlAttributes[$attribute];
	}

	public function getHTMLAttributesAsString(): string {
		if(empty($this->htmlAttributes)) {
			$attributes = '';
		} else {
			$attributes = ' ' . implode(' ', array_map(function ($value, $key) {
				return $key . '="' . $value . '"';
			}, array_values($this->htmlAttributes), array_keys($this->htmlAttributes)));
		}

		return $attributes;
	}

	public function setHTMLAttribute(string $attribute, string $value): self {
		$this->htmlAttributes[$attribute] = $value;
		return $this;
	}

	public function clearHTMLAttributes(): self {
		$this->htmlAttributes = array();
		return $this;
	}

	public final function getParentFieldset(): FormFieldset {
		return $this->parentFieldset;
	}

	public final function setParentFieldset(AbstractFormFieldset $parentFieldset): self {
		$this->parentFieldset = $parentFieldset;
		return $this;
	}

}

