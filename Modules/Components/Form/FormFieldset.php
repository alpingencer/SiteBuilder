<?php

namespace SiteBuilder\Modules\Components\Form;

use SiteBuilder\Modules\Database\DatabaseModule;

class FormFieldset extends AbstractFormFieldset {

	public static function init(string $prompt): FormFieldset {
		return new self($prompt);
	}

	private function __construct(string $prompt) {
		parent::__construct($prompt);
	}

	public function getContent(): string {
		// Generate prompt HTML
		$html = '<tr><td>' . $this->getPrompt() . ':</td>';

		// Generate fieldset HTML
		$html .= '<td>';
		$html .= '<fieldset>';

		// Generate form field HTML
		foreach($this->getFormFields() as $field) {
			if($this->getParentForm()->isNewObject()) {
				$prefillValue = $field->getDefaultValue();
			} else {
				// Fetch existing data from database
				$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();
				$table = $this->getParentForm()->getMainTableDatabaseName();
				$id = $this->getParentForm()->getObjectID();
				$column = $field->getColumn();
				$key = $this->getParentForm()->getPrimaryKey();
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
		foreach($this->getFormFields() as $formField) {
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

}
