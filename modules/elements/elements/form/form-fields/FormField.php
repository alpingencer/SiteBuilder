<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Database\DatabaseComponent;

abstract class FormField {
	private $parentFieldset;
	private $formFieldName;
	private $column;
	private $defaultValue;
	private $isAutoFocus;
	private $isDisabled;
	private $isRequired;

	public function __construct(string $formFieldName, string $column, string $defaultValue) {
		$this->setColumn($column);
		$this->setFormFieldName($formFieldName);
		$this->setDefaultValue($defaultValue);
		$this->setAutoFocus(false);
		$this->setDisabled(false);
		$this->setRequired(false);
	}

	public abstract function getDependencies(): array;

	public abstract function getContent(): string;

	public function prefill(): string {
		if($this->getGrandparentForm()->isNewForm()) {
			// Show default placeholder values
			return $this->defaultValue;
		} else {
			// Show existing values from database
			$database = $GLOBALS['__SiteBuilder_Core']->getCurrentPage()->getComponentByClass(DatabaseComponent::class);

			if($this->parentFieldset->isManyField()) {
				$table = $this->parentFieldset->getSecondaryTableDatabaseName();
				$key = $this->parentFieldset->getForeignKey();
			} else {
				$table = $this->getGrandparentForm()->getTableDatabaseName();
				$key = $this->getGrandparentForm()->getPrimaryKey();
			}

			$return = $database->getVal($table, $this->getGrandparentForm()->getObjectID(), $this->column, $key);
			if(empty($return)) {
				return $this->defaultValue;
			} else {
				return $return;
			}
		}
	}

	public function setParentFieldset(FormFieldset $parentFieldset): self {
		$this->parentFieldset = $parentFieldset;
		return $this;
	}

	public function getParentFieldset(): FormFieldset {
		return $this->parentFieldset;
	}

	public function getGrandparentForm(): FormElement {
		return $this->parentFieldset->getParentForm();
	}

	public function setFormFieldName(string $formFieldName): self {
		$this->formFieldName = $formFieldName;
		return $this;
	}

	public function getFormFieldName(): string {
		return $this->formFieldName;
	}

	public function setColumn(string $column): self {
		$this->column = $column;
		return $this;
	}

	public function getColumn(): string {
		return $this->column;
	}

	public function setDefaultValue(string $defaultValue): self {
		$this->defaultValue = $defaultValue;
		return $this;
	}

	public function getDefaultValue(): string {
		return $this->defaultValue;
	}

	public function setAutoFocus(bool $isAutoFocus): self {
		$this->isAutoFocus = $isAutoFocus;
		return $this;
	}

	public function isAutoFocus(): bool {
		return $this->isAutoFocus;
	}

	public function setDisabled(bool $isDisabled): self {
		$this->isDisabled = $isDisabled;
		return $this;
	}

	public function isDisabled(): bool {
		return $this->isDisabled;
	}

	public function setRequired(bool $isRequired): self {
		$this->isRequired = $isRequired;
		return $this;
	}

	public function isRequired(): bool {
		return $this->isRequired;
	}

}
