<?php

namespace SiteBuilder\PageElement;

use ErrorException;

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
