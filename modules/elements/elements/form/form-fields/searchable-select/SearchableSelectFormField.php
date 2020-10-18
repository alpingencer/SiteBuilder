<?php

namespace SiteBuilder\Elements;

class SearchableSelectField extends FormField {
	private $placeholderText;
	private $options;

	public static function newInstance(string $formFieldName, string $column): self {
		return new self($formFieldName, $column);
	}

	public function __construct(string $formFieldName, string $column) {
		parent::__construct($formFieldName, $column, '');
		$this->placeholderText = '';
		$this->options = array();
	}

	public function getDependencies(): array {
		return array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'jquery/jquery-3.5.1.min.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'jquery/jquery.actual.min.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/form/form-fields/searchable-select/searchable-select.js', 'defer'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/form/form-fields/searchable-select/searchable-select.css')
		);
	}

	public function getContent(): string {
		$formFieldName = $this->getFormFieldName();
// 		$autoFocus = ($this->isAutoFocus()) ? ' autofocus' : '';
		$disabled = ($this->isDisabled()) ? ' disabled' : '';
		$required = ($this->isRequired()) ? ' required' : '';

		$html = '<select name="' . $formFieldName . '" class="sitebuilder-searchable-select"' . $disabled . $required . '>';

		foreach($this->options as $option) {
			$selected = ($option['selected']) ? ' selected="selected"' : '';
			$html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['prompt'] . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	public function prefill(): string {
		return '';
	}

	public function addOption(string $prompt, string $value, bool $selected = false): self {
		array_push($this->options, array(
				'prompt' => $prompt,
				'value' => $value,
				'selected' => $selected
		));
		return $this;
	}

	public function setPlaceholderText(string $placeholderText): self {
		$this->placeholderText = $placeholderText;
		return $this;
	}

	public function clearPlaceholderText(): self {
		$this->setPlaceholderText('');
		return $this;
	}

	public function getPlaceholderText(): string {
		return $this->placeholderText;
	}

	public function clearOptions(): self {
		$this->options = array();
		return $this;
	}

	public function getOptions(): array {
		return $this->options;
	}

}
