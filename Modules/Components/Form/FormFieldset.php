<?php

namespace SiteBuilder\Modules\Components\Form;

use SiteBuilder\Core\CM\Dependency\Dependency;
use SiteBuilder\Modules\Components\Form\FormField\FormField;
use SiteBuilder\Modules\Database\DatabaseModule;

class FormFieldset {
	private $prompt;
	private $parentForm;
	private $formFields;

	public static function init(string $prompt): FormFieldset {
		return new self($prompt);
	}

	protected function __construct(string $prompt) {
		$this->setPrompt($prompt);
		$this->clearFormFields();
	}

	public function getDependencies(): array {
		// Get form field dependencies
		$formFieldDependencies = array();
		foreach($this->formFields as $formField) {
			$formFieldDependencies = array_merge($formFieldDependencies, $formField->getDependencies());
		}

		// Remove duplicates
		Dependency::removeDuplicates($formFieldDependencies);

		return $formFieldDependencies;
	}

	public function getContent(): string {
		// Generate prompt HTML
		$html = '<tr><td>' . $this->prompt . ':</td>';

		// Generate fieldset HTML
		$html .= '<td>';
		$html .= '<fieldset>';

		// Generate form field HTML
		foreach($this->formFields as $field) {
			if($this->parentForm->isNewObject()) {
				$prefillValue = $field->getDefaultValue();
			} else {
				// Fetch existing data from database
				$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();
				$table = $this->parentForm->getMainTableDatabaseName();
				$id = $this->parentForm->getObjectID();
				$column = $field->getColumn();
				$key = $this->parentForm->getPrimaryKey();
				$prefillValue = $database->getVal($table, $id, $column, $key);
			}

			$html .= $field->getContent($prefillValue);
		}

		$html .= '</fieldset></td></tr>';
		return $html;
	}

	public function process(): array {
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

	public function delete(): void {
		// Return: Nothing to do
		return;
	}

	public function getPrompt(): string {
		return $this->prompt;
	}

	private function setPrompt(string $prompt): void {
		$this->prompt = $prompt;
	}

	public function getParentForm(): FormComponent {
		return $this->parentForm;
	}

	public function setParentForm(FormComponent $parentForm): self {
		$this->parentForm = $parentForm;
		return $this;
	}

	public function addFormField(FormField $formField): self {
		$formField->setParentFieldset($this);
		array_push($this->formFields, $formField);
		return $this;
	}

	public function getFormFields(): array {
		return $this->formFields;
	}

	public function clearFormFields(): self {
		$this->formFields = array();
		return $this;
	}

}

