<?php

namespace SiteBuilder\Elements;

use ErrorException;
use SiteBuilder\Database\DatabaseComponent;

class FormElement extends Element {
	private $isNewForm;
	private $objectID;
	private $tableDatabaseName;
	private $primaryKey;
	private $formName;
	private $fieldsets;
	private $autoDelete;
	private $autoProcess;
	private $showDelete;
	private $deleteText;
	private $submitText;

	public static function newInstance(string $formName, string $tableDatabaseName): self {
		return new self($formName, $tableDatabaseName);
	}

	public function __construct(string $formName, string $tableDatabaseName) {
		$this->setIsNewForm(true);
		$this->setTableDatabaseName($tableDatabaseName);
		$this->setFormName($formName);
		$this->clearPrimaryKey();
		$this->clearFieldsets();
		$this->setAutoDelete(true);
		$this->setAutoProcess(true);
		$this->clearDeleteText();
		$this->clearSubmitText();

		// Check if the current page has a DatabaseComponent
		if(!$GLOBALS['__SiteBuilder_Core']->getCurrentPage()->hasComponentsByClass(DatabaseComponent::class)) {
			throw new ErrorException("No DatabaseComponent found when using a FormElement!");
		}
	}

	public function getDependencies(): array {
		$formDependencies = array(
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/form/forms.css')
		);

		// Get fieldset dependencies
		$fieldsetDependencies = array();
		foreach($this->fieldsets as $fieldset) {
			$fieldsetDependencies = array_merge($fieldsetDependencies, $fieldset->getDependencies());
		}

		// Merge dependencies and remove duplicates
		$dependencies = array_merge($formDependencies, $fieldsetDependencies);
		Dependency::removeDuplicates($dependencies);

		return $dependencies;
	}

	public function getContent(): string {
		// Delete form
		if($this->autoDelete && isset($_POST['__SiteBuilder_DeleteForm_' . $this->formName])) {
			$this->delete();
		}

		// Proccess form
		if($this->autoProcess && isset($_POST['__SiteBuilder_SubmitForm_' . $this->formName])) {
			$this->process();
		}

		$html = '<form class="sitebuilder-form" method="POST" enctype="multipart/form-data"><table>';

		// Generate fieldset html
		$html .= '<tbody>';
		foreach($this->fieldsets as $fieldset) {
			$html .= $fieldset->getContent();
		}
		$html .= '</tbody>';

		// Generate submit and delete button html
		$html .= '<tfoot><tr>';

		if($this->showDelete) {
			$html .= '<td><input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_DeleteForm_' . $this->formName . '" value="' . $this->deleteText . '"></td>';
			$html .= '<td>';
		} else {
			$html .= '<td colspan="2">';
		}
		$html .= '<input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_SubmitForm_' . $this->formName . '" value="' . $this->submitText . '">';
		$html .= '</td>';

		$html .= '</tr></tfoot>';
		$html .= '</table></form>';

		return $html;
	}

	public function process(): void {
		// Get values from fieldsets that aren't manyField
		$values = array();
		foreach($this->fieldsets as $fieldset) {
			if(!$fieldset->isManyField()) {
				$values = array_merge($values, $fieldset->process());
			}
		}

		$database = $GLOBALS['__SiteBuilder_Core']->getCurrentPage()->getComponentByClass(DatabaseComponent::class);

		if($this->isNewForm) {
			// Create new object
			$objectID = $database->insert($this->tableDatabaseName, $values, $this->primaryKey);
			$this->setObjectID($objectID);
		} else {
			// Update old object
			$database->update($this->tableDatabaseName, $values, '`' . $this->primaryKey . "`='" . $this->objectID . "'");
		}

		// Proccess fieldsets that are manyField
		foreach($this->fieldsets as $fieldset) {
			if($fieldset->isManyField()) {
				$fieldset->process();
			}
		}
	}

	public function delete(): void {
		if($this->isNewForm) {
			// Cannot delete new form
			throw new ErrorException("Cannot delete form with an empty object ID!");
		}

		// Delete entries in secondary tables
		foreach($this->fieldsets as $fieldset) {
			if($fieldset->isManyField()) {
				$fieldset->delete();
			}
		}

		// Delete entry in main table
		$database = $GLOBALS['__SiteBuilder_Core']->getCurrentPage()->getComponentByClass(DatabaseComponent::class);
		$database->delete($this->tableDatabaseName, $this->primaryKey . "='" . $this->objectID . "'");
	}

	public function addFieldset(FormFieldset $fieldset): self {
		$fieldset->setParentForm($this);
		array_push($this->fieldsets, $fieldset);
		return $this;
	}

	public function setIsNewForm(bool $isNewForm, string $objectID = ''): self {
		$this->isNewForm = $isNewForm;

		if(!$this->isNewForm) {
			$this->setObjectID($objectID);
			$this->setShowDelete(true);
		}

		return $this;
	}

	public function isNewForm(): bool {
		return $this->isNewForm;
	}

	private function setObjectID(string $objectID): self {
		if(empty($objectID)) {
			throw new ErrorException("The given object ID must not be empty!");
		}

		$this->objectID = $objectID;
		return $this;
	}

	public function clearObjectID(): self {
		$this->setObjectID('');
		return $this;
	}

	public function getObjectID(): string {
		return $this->objectID;
	}

	public function setTableDatabaseName(string $tableDatabaseName): self {
		if(empty($tableDatabaseName)) {
			throw new ErrorException("The given table database name must not be empty!");
		}

		$this->tableDatabaseName = $tableDatabaseName;
		return $this;
	}

	public function getTableDatabaseName(): string {
		return $this->tableDatabaseName;
	}

	public function setPrimaryKey(string $primaryKey): self {
		$this->primaryKey = $primaryKey;
		return $this;
	}

	public function clearPrimaryKey(): self {
		$this->setPrimaryKey('ID');
		return $this;
	}

	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	public function setFormName(string $formName): self {
		if(empty($formName)) {
			throw new ErrorException("The given form name must not be empty!");
		}

		$this->formName = $formName;
		return $this;
	}

	public function getFormName(): string {
		return $this->formName;
	}

	public function clearFieldsets(): self {
		$this->fieldsets = array();
		return $this;
	}

	public function getFieldsets(): array {
		return $this->fieldsets;
	}

	public function setAutoDelete(bool $autoDelete): self {
		$this->autoDelete = $autoDelete;
		return $this;
	}

	public function getAutoDelete(): bool {
		return $this->autoDelete;
	}

	public function setAutoProcess(bool $autoProcess): self {
		$this->autoProcess = $autoProcess;
		return $this;
	}

	public function getAutoProcess(): bool {
		return $this->autoProcess;
	}

	public function setShowDelete(bool $showDelete): self {
		if($showDelete && $this->isNewForm) {
			throw new ErrorException("Cannot show delete button in form with an empty object ID!");
		}

		$this->showDelete = $showDelete;
		return $this;
	}

	public function getShowDelete(): bool {
		return $this->showDelete;
	}

	public function setDeleteText(string $deleteText): self {
		$this->deleteText = $deleteText;
		return $this;
	}

	public function clearDeleteText(): self {
		$this->setDeleteText('Delete');
		return $this;
	}

	public function getDeleteText(): string {
		return $this->deleteText;
	}

	public function setSubmitText(string $submitText): self {
		$this->submitText = $submitText;
		return $this;
	}

	public function clearSubmitText(): self {
		$this->setSubmitText('Submit');
		return $this;
	}

	public function getSubmitText(): string {
		return $this->submitText;
	}

}
