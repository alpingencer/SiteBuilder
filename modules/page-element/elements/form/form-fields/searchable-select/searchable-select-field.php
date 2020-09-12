<?php

namespace SiteBuilder\PageElement;

class SearchableSelectField extends FormField {
	private $name;
	private $classes;
	private $id;
	private $placeholderText;
	private $options;

	public static function newInstance(string $name): self {
		return new self($name);
	}

	public function __construct(string $name) {
		$this->name = $name;
		$this->classes = '';
		$this->id = '';
		$this->placeholderText = 'Type to search...';
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
		$id = (empty($this->id)) ? '' : ' id="' . $this->id . '"';
		$classes = 'sitebuilder-searchable-select';
		if(!empty($this->classes)) $classes .= ' ' . $this->classes;
		$html = '<select' . $id . ' class="' . $classes . '" name="' . $this->name . '" data-placeholder-text="' . $this->placeholderText . '">';

		foreach($this->options as $option) {
			$selected = ($option['selected']) ? ' selected="selected"' : '';
			$html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['prompt'] . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setClasses(string $classes): self {
		$this->classes = $classes;
		return $this;
	}

	public function getClasses(): string {
		return $this->classes;
	}

	public function setID(string $id): self {
		$this->id = $id;
		return $this;
	}

	public function getID(): string {
		return $this->id;
	}

	public function setPlaceholderText(string $placeholderText): self {
		$this->placeholderText = $placeholderText;
		return $this;
	}

	public function getPlaceholderText(): string {
		return $this->placeholderText;
	}

	public function addOption(string $prompt, string $value, bool $selected = false): self {
		array_push($this->options, array(
				'prompt' => $prompt,
				'value' => $value,
				'selected' => $selected
		));
		return $this;
	}

}
