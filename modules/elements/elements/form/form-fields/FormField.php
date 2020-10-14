<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Database\DatabaseComponent;

abstract class FormField {
	private $parentFieldset;
	private $formFieldName;
	private $column;
	private $defaultValue;

	public function __construct(string $formFieldName, string $column, string $defaultValue) {
		$this->setColumn($column);
		$this->setFormFieldName($formFieldName);
		$this->setDefaultValue($defaultValue);
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

}
