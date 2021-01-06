<?php

namespace SiteBuilder\Modules\Components\Form;

use SiteBuilder\Modules\Database\DatabaseModule;

class FormFieldset extends AbstractFormFieldset {

	public static function init(string $prompt): FormFieldset {
		return new self($prompt);
	}

	protected function __construct(string $prompt) {
		parent::__construct($prompt);
	}

	public function getContent(): string {
		$isReadOnly = $this->getParentForm()->isReadOnly();
		$prompt = (empty($this->getPrompt())) ? '' : $this->getPrompt() . ':';

		// Generate prompt HTML
		$html = '<tr><td>' . $prompt . '</td>';

		// Generate fieldset HTML
		$html .= '<td>';

		if(!$isReadOnly) {
			$html .= '<fieldset>';
		}

		if(!$this->getParentForm()->isNewObject()) {
			// Fetch existing data from database
			$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();
			$table = $this->getParentForm()->getMainTableDatabaseName();
			$id = $this->getParentForm()->getObjectID();
			$prefillValues = $database->getRow($table, $id);
		}

		// Generate form field HTML
		foreach($this->getFormFields() as $field) {
			if($this->getParentForm()->isNewObject()) {
				$prefillValue = $field->getDefaultValue();
			} else {
				$prefillValue = $prefillValues[$field->getColumn()] ?? '';
			}

			$html .= $field->getContent($prefillValue, $isReadOnly);
		}

		if(!$isReadOnly) {
			$html .= '</fieldset>';
		}

		$html .= '</td></tr>';
		return $html;
	}

	public function process(): array {
		// Get values from form fields
		$values = array();
		foreach($this->getFormFields() as $formField) {
			$values = array_merge($values, array(
					$formField->getColumn() => $_POST[$formField->getFormFieldName()] ?? null
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
