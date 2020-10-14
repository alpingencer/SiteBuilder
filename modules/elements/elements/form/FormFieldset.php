<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Database\DatabaseComponent;
use ErrorException;

class FormFieldset {
	private $prompt;
	private $parentForm;
	private $formFields;
	private $isManyField;
	private $secondaryTableDatabaseName;
	private $minNumFields;
	private $maxNumFields;
	private $primaryKey;
	private $foreignKey;

	public static function newInstace(string $prompt): self {
		return new self($prompt);
	}

	public function __construct(string $prompt) {
		$this->setPrompt($prompt);
		$this->clearFormFields();
		$this->setIsManyField(false);
	}

	public function getDependencies(): array {
		if($this->isManyField) {
			$fieldsetDependencies = array(
					new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/form/many-fields.js', 'defer'),
					new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/form/many-fields.css')
			);
		} else {
			$fieldsetDependencies = array();
		}

		// Get form field dependencies
		$formFieldDependencies = array();
		foreach($this->formFields as $formField) {
			$formFieldDependencies = array_merge($formFieldDependencies, $formField->getDependencies());
		}

		// Merge dependencies and remove duplicates
		$dependencies = array_merge($fieldsetDependencies, $formFieldDependencies);
		Dependency::removeDuplicates($dependencies);

		return $dependencies;
	}

	public function getContent(): string {
		$html = '<tr><td>' . $this->prompt . ':</td>';

		// Generate fieldset HTML
		if($this->isManyField) {
			$minNumFields = ($this->minNumFields !== 0) ? ' data-min-fields="' . $this->minNumFields . '"' : '';
			$maxNumFields = ($this->maxNumFields !== 0) ? ' data-max-fields="' . $this->maxNumFields . '"' : '';

			$html .= '<td class="sitebuilder-many-fields"' . $minNumFields . $maxNumFields . '>';
			$html .= '<fieldset class="sitebuilder-template-fieldset">';
		} else {
			$html .= '<td>';
			$html .= '<fieldset>';
		}

		// Generate form field HTML
		foreach($this->formFields as $field) {
			$html .= $field->getContent();
		}

		$html .= '</fieldset></td></tr>';
		return $html;
	}

	public function process(): array {
		if($this->isManyField) {
			// Delete previous entries
			$this->delete();

			// Get database component
			$database = $GLOBALS['__SiteBuilder_Core']->getCurrentPage()->getComponentByClass(DatabaseComponent::class);

			// For each defined fieldset
			// Check first added form field post variable to search for additional fieldsets
			for($i = 1; isset($_POST[$this->formFields[0]->getFormFieldName() . '_' . $i]); $i++) {
				// Add foreign ID
				$values = array(
						$this->foreignKey => $this->parentForm->getObjectID()
				);

				// Add form field values
				foreach($this->formFields as $field) {
					$values = array_merge($values, array(
							$field->getColumn() => $_POST[$field->getFormFieldName() . '_' . $i]
					));
				}

				// Insert new entries
				$database->insert($this->secondaryTableDatabaseName, $values, $this->primaryKey);
			}

			// FormElement has nothing to process, return empty array
			return array();
		} else {
			// Get values from form fields
			$values = array();
			foreach($this->formFields as $formField) {
				$values = array_merge($values, array(
						$formField->getColumn() => $_POST[$formField->getFormFieldName()]
				));
			}

			// Return form field values so that the parent form inserts them into the main table
			return $values;
		}
	}

	public function delete(): void {
		// If fieldset isn't ManyField, nothing to do, return
		if(!$this->isManyField) return;

		// Delete entries in secondary table
		$database = $GLOBALS['__SiteBuilder_Core']->getCurrentPage()->getComponentByClass(DatabaseComponent::class);
		$database->delete($this->secondaryTableDatabaseName, $this->foreignKey . "='" . $this->parentForm->getObjectID() . "'");
	}

	public function addFormField(FormField $formField): self {
		$formField->setParentFieldset($this);
		array_push($this->formFields, $formField);
		return $this;
	}

	public function setPrompt(string $prompt): self {
		$this->prompt = $prompt;
		return $this;
	}

	public function getPrompt(): string {
		return $this->prompt;
	}

	public function setParentForm(FormElement $parentForm): self {
		$this->parentForm = $parentForm;
		return $this;
	}

	public function getParentForm(): FormElement {
		return $this->parentForm;
	}

	public function clearFormFields(): self {
		$this->formFields = array();
		return $this;
	}

	public function getFormFields(): array {
		return $this->formFields;
	}

	public function setIsManyField(bool $isManyField, string $secondaryTableDatabaseName = '', int $minNumFields = 0, int $maxNumFields = 0): self {
		$this->isManyField = $isManyField;

		if($this->isManyField) {
			$this->setSecondaryTableDatabaseName($secondaryTableDatabaseName);
			$this->setMinNumFields($minNumFields);
			$this->setMaxNumFields($maxNumFields);
		} else {
			unset($this->secondaryTableDatabaseName);
			unset($this->minNumFields);
			unset($this->maxNumFields);
			$this->clearPrimaryKey();
			$this->clearForeignKey();
		}

		return $this;
	}

	public function isManyField(): bool {
		return $this->isManyField;
	}

	public function setSecondaryTableDatabaseName(string $secondaryTableDatabaseName): self {
		if(empty($secondaryTableDatabaseName)) {
			throw new ErrorException("The given secondary table database name must not be empty!");
		}

		$this->secondaryTableDatabaseName = $secondaryTableDatabaseName;
		return $this;
	}

	public function getSecondaryTableDatabaseName(): string {
		if(!$this->isManyField) {
			throw new ErrorException("This fieldset is not of type ManyField!");
		}

		return $this->secondaryTableDatabaseName;
	}

	public function setMinNumFields(int $minNumFields): self {
		if($minNumFields < 0) {
			throw new ErrorException("The minimum number of fields must not be smaller than 0!");
		}

		$this->minNumFields = $minNumFields;
		return $this;
	}

	public function clearMinNumFields(): self {
		$this->setMinNumFields(0);
		return $this;
	}

	public function getMinNumFields(): int {
		if(!$this->isManyField) {
			throw new ErrorException("This fieldset is not of type ManyField!");
		}

		return $this->minNumFields;
	}

	public function setMaxNumFields(int $maxNumFields): self {
		if($maxNumFields !== 0 && $maxNumFields < $this->minNumFields) {
			throw new ErrorException("The maximum number of fields must not be smaller than the minimum number of fields!");
		}

		$this->maxNumFields = $maxNumFields;
		return $this;
	}

	public function clearMaxNumFields(): self {
		$this->setMaxNumFields(0);
		return $this;
	}

	public function getMaxNumFields(): int {
		if(!$this->isManyField) {
			throw new ErrorException("This fieldset is not of type ManyField!");
		}

		return $this->maxNumFields;
	}

	public function setPrimaryKey(string $primaryKey): self {
		$this->primaryKey = $primaryKey;
		return $this;
	}

	public function clearPrimaryKey(): self {
		$this->setPrimaryKey('ID');
		return $this;
	}

	public function getPrimaryKey(): string {
		if(!$this->isManyField) {
			throw new ErrorException("This fieldset is not of type ManyField!");
		}

		return $this->primaryKey;
	}

	public function setForeignKey(string $foreignKey): self {
		$this->foreignKey = $foreignKey;
		return $this;
	}

	public function clearForeignKey(): self {
		$this->setForeignKey('FOREIGN_KEY');
		return $this;
	}

	public function getForeignKey(): string {
		if(!$this->isManyField) {
			throw new ErrorException("This fieldset is not of type ManyField!");
		}

		return $this->foreignKey;
	}

}
