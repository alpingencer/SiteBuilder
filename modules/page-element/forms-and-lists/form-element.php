<?php

namespace SiteBuilder\PageElement;

use ErrorException;

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
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'forms-and-lists/css/forms.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/jquery/jquery-3.5.1.min.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'forms-and-lists/js/many-fields.js', 'defer'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'forms-and-lists/css/many-fields.css')
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
	private $isManyField;
	private $minNumFields, $maxNumFields;
	private $fields;

	public static function newInstance(string $prompt): self {
		return new self($prompt);
	}

	public function __construct(string $prompt) {
		$this->prompt = $prompt;
		$this->isManyField = false;
		$this->minNumFields = 0;
		$this->maxNumFields = 0;
		$this->fields = array();
	}

	public function getPrompt(): string {
		return $this->prompt;
	}

	public function setIsManyField(bool $isManyField, int $minNumFields = 0, int $maxNumFields = 0): self {
		if($minNumFields < 0) throw new ErrorException('$minNumFields must not be negative!');
		if($maxNumFields !== 0 && $maxNumFields < $minNumFields) throw new ErrorException('$maxNumFields must not be smaller than $minNumFields!');

		$this->isManyField = $isManyField;
		$this->minNumFields = $minNumFields;
		$this->maxNumFields = $maxNumFields;
		return $this;
	}

	public function isManyField(): bool {
		return $this->isManyField;
	}

	public function getMinNumFields(): int {
		return $this->minNumFields;
	}

	public function getMaxNumFields(): int {
		return $this->maxNumFields;
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
