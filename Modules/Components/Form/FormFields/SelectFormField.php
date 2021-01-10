<?php

namespace SiteBuilder\Modules\Components\Form\FormFields;

use SiteBuilder\Core\CM\Dependencies\CSSDependency;
use SiteBuilder\Core\CM\Dependencies\JSDependency;
use SiteBuilder\Modules\Components\Form\FormField;
use SiteBuilder\Modules\Database\DatabaseModule;

class SelectFormField extends FormField {
	private $options;
	private $isSearchable;

	public static function init(string $formFieldName, string $column, string $defaultValue): SelectFormField {
		return new self($formFieldName, $column, $defaultValue);
	}

	protected function __construct(string $formFieldName, string $column, string $defaultValue) {
		parent::__construct($formFieldName, $column, $defaultValue);
		$this->clearOptions();
		$this->setIsSearchable(false);
	}

	public function getDependencies(): array {
		if($this->isSearchable) {
			return array(
					JSDependency::init('External/jQuery/jquery-3.5.1.min.js'),
					JSDependency::init('External/jQuery/jquery.actual.min.js'),
					JSDependency::init('Form/SelectFormField/searchable-select.js', 'defer'),
					CSSDependency::init('Form/SelectFormField/searchable-select.css')
			);
		} else {
			return array();
		}
	}

	public function getContent(string $prefillValue, bool $isReadOnly, string $formFieldNameSuffix = ''): string {
		$name = $this->getFormFieldName() . $formFieldNameSuffix;
		$classes = ($this->isSearchable) ? ' class="sitebuilder-searchable-select"' : '';
		$disabled = ($isReadOnly) ? ' disabled' : '';
		$attributes = $this->getHTMLAttributesAsString();

		$html = '<select name="' . $name . '"' . $classes . $disabled . $attributes . '>';

		foreach($this->options as $option) {
			$selected = ($prefillValue === $option['value']) ? ' selected' : '';
			$html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['prompt'] . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	public function getOptions(): array {
		return $this->options;
	}

	public function addOption(string $prompt, string $value): self {
		array_push($this->options, array(
				'prompt' => $prompt,
				'value' => $value
		));
		return $this;
	}

	public function addOptionsFromDatabase(string $table, string $valueColumn, string $promptColumn, string $condition = '1', string $order = ''): self {
		$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();
		$result = $database->getRows($table, $condition, "$valueColumn,$promptColumn", $order);

		foreach($result as $row) {
			$this->addOption($row[$promptColumn], $row[$valueColumn]);
		}

		return $this;
	}

	public function addOptionsByQuery(string $query): self {
		$result = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db()->getRowsByQuery($query);

		foreach($result as $row) {
			$valueColumn = array_key_first($row);
			$promptColumn = array_key_last($row);
			$this->addOption($row[$promptColumn], $row[$valueColumn]);
		}

		return $this;
	}

	public function clearOptions(): self {
		$this->options = array();
		return $this;
	}

	public function isSearchable(): bool {
		return $this->isSearchable;
	}

	public function setIsSearchable(bool $isSearchable): self {
		$this->isSearchable = $isSearchable;
		return $this;
	}

}

