<?php

namespace SiteBuilder\Modules\Components\Form;

use ErrorException;
use SiteBuilder\Core\CM\Dependencies\CSSDependency;
use SiteBuilder\Core\CM\Dependencies\JSDependency;

class ManyFieldFormFieldset extends AbstractFormFieldset {
	private $secondaryTableDatabaseName;
	private $minNumFields;
	private $maxNumFields;
	private $queryCriteria;
	private $fieldsetOrder;
	private $primaryKey;
	private $foreignKey;

	public static function init(string $prompt, string $secondaryTableDatabaseName): ManyFieldFormFieldset {
		return new self($prompt, $secondaryTableDatabaseName);
	}

	protected function __construct(string $prompt, string $secondaryTableDatabaseName) {
		parent::__construct($prompt);
		$this->setSecondaryTableDatabaseName($secondaryTableDatabaseName);
		$this->clearMinNumFields();
		$this->clearMaxNumFields();
		$this->clearQueryCriteria();
		$this->clearFieldsetOrder();
	}

	public function getDependencies(): array {
		$manyFieldDependencies = array(
				JSDependency::init('Form/many-fields.js', 'defer'),
				CSSDependency::init('Form/many-fields.css')
		);

		return array_merge($manyFieldDependencies, parent::getDependencies());
	}

	public function getContent(): string {
		$isReadOnly = $this->getParentForm()->isReadOnly();
		$prompt = (empty($this->getPrompt())) ? '' : $this->getPrompt() . ':';

		// Generate prompt HTML
		$html = '<tr><td>' . $prompt . '</td>';


		// Generate fieldset HTML
		$minNumFields = ($this->minNumFields !== 0) ? ' data-min-fields="' . $this->minNumFields . '"' : '';
		$maxNumFields = ($this->maxNumFields !== 0) ? ' data-max-fields="' . $this->maxNumFields . '"' : '';

		$html .= '<td class="sitebuilder-many-fields"' . $minNumFields . $maxNumFields . '>';

		// Generate template fieldset HTML
		if(!$isReadOnly) {
			$html .= '<fieldset class="sitebuilder-template-fieldset">';

			foreach($this->getFormFields() as $field) {
				$html .= $field->getContent($field->getDefaultValue(), $isReadOnly);
			}

			$html .= '</fieldset>';
		}

		// Generate existing fieldset HTML
		if($this->getParentForm()->isNewObject()) {
			$count = $this->minNumFields;
		} else {
			$table = $this->secondaryTableDatabaseName;
			$condition = "`" . $this->getForeignKey() . "`='" . $this->getParentForm()->getObjectID() . "'";
			if(!empty($this->queryCriteria)) $condition .= ' AND ' . $this->queryCriteria;
			$order = (empty($this->fieldsetOrder)) ? $this->getPrimaryKey() : $this->fieldsetOrder;
			$rows = $this->getParentForm()->getDatabase()->getRows($table, $condition, '*', $order);
			$count = max($this->minNumFields, count($rows));
		}

		for($i = 0; $i < $count; $i++ ) {
			$html .= '<fieldset>';

			foreach($this->getFormFields() as $field) {
				if($this->getParentForm()->isNewObject() || !isset($rows[$i])) {
					$prefillValue = $field->getDefaultValue();
				} else {
					// Fetch existing data from database
					$prefillValue = $rows[$i][$field->getColumn()] ?? '';
				}

				$html .= $field->getContent($prefillValue, $isReadOnly, '_' . ($i + 1));
			}

			$html .= '</fieldset>';
		}


		$html .= '</td></tr>';
		return $html;
	}

	public function process(): array {
		// Delete previous entries
		if(!$this->getParentForm()->isNewObject()) {
			$this->delete();
		}

		// If there are no form fields, return
		if(empty($this->getFormFields())) {
			return array();
		}

		// For each defined fieldset
		// Check first added form field post variable to search for additional fieldsets
		for($i = 1; isset($_POST[$this->getFormFields()[0]->getFormFieldName() . '_' . $i]); $i++ ) {
			// Add foreign ID
			$values = array(
					$this->getForeignKey() => $this->getParentForm()->getObjectID()
			);

			// Add form field values
			foreach($this->getFormFields() as $field) {
				$value = $_POST[$field->getFormFieldName() . '_' . $i] ?? null;
				if(empty($value)) $value = null;
				$values = array_merge($values, array(
						$field->getColumn() => $value
				));
			}

			// Insert new entries
			$this->getParentForm()->getDatabase()->insert($this->secondaryTableDatabaseName, $values);
		}

		// Parent form has nothing to process, return empty array
		return array();
	}

	public function delete(): void {
		// Delete entries in secondary table
		$table = $this->secondaryTableDatabaseName;
		$condition = "`" . $this->getForeignKey() . "`='" . $this->getParentForm()->getObjectID() . "'";
		if(!empty($this->queryCriteria)) $condition .= ' AND ' . $this->queryCriteria;
		$this->getParentForm()->getDatabase()->delete($table, $condition);
	}

	public function getSecondaryTableDatabaseName(): string {
		return $this->secondaryTableDatabaseName;
	}

	private function setSecondaryTableDatabaseName(string $secondaryTableDatabaseName): void {
		$this->secondaryTableDatabaseName = $secondaryTableDatabaseName;
	}

	public function getMinNumFields(): int {
		return $this->minNumFields;
	}

	public function setMinNumFields(int $minNumFields): self {
		// Check if the given minimum number of fields is less than 0
		// If yes, throw error: Minimum number cannot be negative
		if($minNumFields < 0) {
			throw new ErrorException("The minimum number of fields must not be smaller than 0!");
		}

		$this->minNumFields = $minNumFields;
		return $this;
	}

	public function clearMinNumFields(): self {
		$this->setMinNumFields(0);
		return $this;
	}

	public function getMaxNumFields(): int {
		return $this->maxNumFields;
	}

	public function setMaxNumFields($maxNumFields): self {
		// Check if the given maximum number of fields is not 0 and less than the minimum
		// If yes, throw error: Maximum cannot be less than minimum
		if($maxNumFields !== 0 && $maxNumFields < $this->minNumFields) {
			throw new ErrorException("The maximum number of fields must not be smaller than the minimum number of fields!");
		}

		$this->maxNumFields = $maxNumFields;
		return $this;
	}

	public function clearMaxNumFields(): self {
		$this->setMaxNumFields(0);
		return $this;
	}

	public function getQueryCriteria(): string {
		return $this->queryCriteria;
	}

	public function setQueryCriteria(string $queryCriteria): self {
		$this->queryCriteria = $queryCriteria;
		return $this;
	}

	public function clearQueryCriteria(): self {
		$this->setQueryCriteria('');
		return $this;
	}

	public function getFieldsetOrder(): string {
		return $this->fieldsetOrder;
	}

	public function setFieldsetOrder(string $fieldsetOrder): self {
		$this->fieldsetOrder = $fieldsetOrder;
		return $this;
	}

	public function clearFieldsetOrder(): self {
		$this->setFieldsetOrder('');
		return $this;
	}

	private function getPrimaryKey(): string {
		if(!isset($this->primaryKey)) {
			$this->primaryKey = $this->getParentForm()->getDatabase()->getPrimaryKey($this->secondaryTableDatabaseName);
		}

		return $this->primaryKey;
	}

	private function getForeignKey(): string {
		if(!isset($this->foreignKey)) {
			$this->foreignKey = $this->getParentForm()->getDatabase()->getForeignKey($this->secondaryTableDatabaseName, $this->getParentForm()->getMainTableDatabaseName());
		}

		return $this->foreignKey;
	}

	public function setForeignKey(string $foreignKey): self {
		$this->foreignKey = $foreignKey;
		return $this;
	}
}

