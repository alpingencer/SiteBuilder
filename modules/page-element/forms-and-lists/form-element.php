<?php

namespace SiteBuilder\PageElement;

class FormElement extends PageElement {
	public $html;
	private $fieldsets;
	private $deleteText, $submitText;
	private $showDelete;
	private $proccessFunction, $deleteFunction;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$dependencies = array(
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'forms-and-lists/css/forms.css')
		);
		parent::__construct($dependencies);
		$this->html = '';
		$this->fieldsets = array();
		$this->deleteText = 'Delete';
		$this->submitText = 'Submit';
		$this->showDelete = true;
		$this->proccessFunction = function () {};
		$this->deleteFunction = function () {};
	}

	public function getContent(): string {
		return $this->html;
	}

	public function addFieldset(FormFieldset $fieldset): self {
		array_push($this->fieldsets, $fieldset);
		return $this;
	}

	public function getFieldsets(): array {
		return $this->fieldsets;
	}

	public function setDeleteText(string $deleteText): self {
		$this->deleteText = $deleteText;
		return $this;
	}

	public function getDeleteText(): string {
		return $this->deleteText;
	}

	public function setSubmitText(string $submitText): self {
		$this->submitText = $submitText;
		return $this;
	}

	public function getSubmitText(): string {
		return $this->submitText;
	}

	public function setShowDelete(bool $showDelete): self {
		$this->showDelete = $showDelete;
		return $this;
	}

	public function getShowDelete(): bool {
		return $this->showDelete;
	}

	public function setProccessFunction(callable $proccessFunction): self {
		$this->proccessFunction = $proccessFunction;
		return $this;
	}

	public function getProccessFunction(): callable {
		return $this->proccessFunction;
	}

	public function setDeleteFunction(callable $deleteFunction): self {
		$this->deleteFunction = $deleteFunction;
		return $this;
	}

	public function getDeleteFunction(): callable {
		return $this->deleteFunction;
	}

}

class FormFieldset {
	private $prompt;
	private $fields;

	public static function newInstance(string $prompt): self {
		return new self($prompt);
	}

	public function __construct(string $prompt) {
		$this->prompt = $prompt;
		$this->fields = array();
	}

	public function getPrompt(): string {
		return $this->prompt;
	}

	public function addField(FormField $field): self {
		array_push($this->fields, $field);
		return $this;
	}

	public function setFields(array $fields): self {
		$this->fields = $fields;
		return $this;
	}

	public function getFields(): array {
		return $this->fields;
	}

}

class FormField {
	private $innerHTML;

	public static function newInstance(string $innerHTML): self {
		return new self($innerHTML);
	}

	public function __construct(string $innerHTML) {
		$this->innerHTML = $innerHTML;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
