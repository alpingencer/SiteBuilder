<?php

namespace SiteBuilder\Modules\Components\Form;

use SiteBuilder\Core\CM\Dependency\Dependency;

abstract class AbstractFormFieldset {
	private $prompt;
	private $parentForm;
	private $formFields;

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

	public abstract function getContent(): string;

	public abstract function process(): array;

	public abstract function delete(): void;

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

