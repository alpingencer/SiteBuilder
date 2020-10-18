<?php

namespace SiteBuilder\Elements;

class TextAreaFormField extends FormField {
	private $cols;
	private $maxLength;
	private $placeHolder;
	private $isReadOnly;
	private $rows;

	public static function newInstance(string $formFieldName, string $column, string $defaultValue): self {
		return new self($formFieldName, $column, $defaultValue);
	}

	public function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->clearCols();
		$this->clearMaxLength();
		$this->clearPlaceHolder();
		$this->setReadOnly(false);
		$this->clearRows();
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		$formFieldName = $this->getFormFieldName();
		$value = $this->prefill();
		$autoFocus = ($this->isAutoFocus()) ? ' autofocus' : '';
		$disabled = ($this->isDisabled()) ? ' disabled' : '';
		$required = ($this->isRequired()) ? ' required' : '';
		$cols = ($this->cols === 0) ? '' : ' cols="' . $this->cols . '"';
		$maxLength = ($this->maxLength === 0) ? '' : ' maxlength="' . $this->maxLength . '"';
		$placeHolder = (empty($this->placeHolder)) ? '' : ' placeholder="' . $this->placeHolder . '"';
		$readOnly = ($this->isReadOnly) ? ' readonly' : '';
		$rows = ($this->rows === 0) ? '' : ' rows="' . $this->rows . '"';

		$html = '<textarea name="' . $formFieldName . '"' . $rows . $cols . $maxLength . $placeHolder . $autoFocus . $disabled . $required . $readOnly . '>';
		$html .= $value;
		$html .= '</textarea>';

		return $html;
	}

	public function setCols(int $cols): self {
		$this->cols = $cols;
		return $this;
	}

	public function clearCols(): self {
		$this->setCols(0);
		return $this;
	}

	public function getCols(): int {
		return $this->cols;
	}

	public function setMaxLength(int $maxLength): self {
		$this->maxLength = $maxLength;
		return $this;
	}

	public function clearMaxLength(): self {
		$this->setMaxLength(0);
		return $this;
	}

	public function getMaxLength(): int {
		return $this->maxLength;
	}

	public function setPlaceHolder(string $placeHolder): self {
		$this->placeHolder = $placeHolder;
		return $this;
	}

	public function clearPlaceHolder(): self {
		$this->setPlaceHolder('');
		return $this;
	}

	public function getPlaceHolder(): string {
		return $this->placeHolder;
	}

	public function setReadOnly(bool $isReadOnly): self {
		$this->isReadOnly = $isReadOnly;
		return $this;
	}

	public function isReadOnly(): bool {
		return $this->isReadOnly;
	}

	public function setRows(int $rows): self {
		$this->rows = $rows;
		return $this;
	}

	public function clearRows(): self {
		$this->setRows(0);
		return $this;
	}

	public function getRows(): int {
		return $this->rows;
	}

}
