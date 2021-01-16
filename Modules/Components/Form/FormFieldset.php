<?php

namespace SiteBuilder\Modules\Components\Form;

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

		// Generate form field HTML
		foreach($this->getFormFields() as $field) {
			if($this->getParentForm()->isNewObject()) {
				// Show default value
				$prefillValue = $field->getDefaultValue();
			} else {
				// Fetch existing data from the database
				$prefillValue = $this->getParentForm()->getPrefillValues()[$field->getColumn()] ?? '';
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
			$value = $_POST[$formField->getFormFieldName()] ?? null;
			if(empty($value)) $value = null;

			$values = array_merge($values, array(
					$formField->getColumn() => $value
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
