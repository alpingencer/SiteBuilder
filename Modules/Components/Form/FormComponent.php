<?php

namespace SiteBuilder\Modules\Components\Form;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\CM\Dependency\CSSDependency;
use SiteBuilder\Core\CM\Dependency\Dependency;
use SiteBuilder\Modules\Database\DatabaseModule;
use ErrorException;

class FormComponent extends Component {
	private $isNewObject;
	private $objectID;
	private $mainTableDatabaseName;
	private $primaryKey;
	private $formName;
	private $fieldsets;
	private $showDelete;
	private $deleteButtonText;
	private $submitButtonText;

	public static function init(string $formName, string $mainTableDatabaseName): FormComponent {
		return new self($formName, $mainTableDatabaseName);
	}

	private function __construct(string $formName, string $mainTableDatabaseName) {
		parent::__construct();
		$this->setIsNewObject(true);
		$this->setMainTableDatabaseName($mainTableDatabaseName);
		$this->clearPrimaryKey();
		$this->setFormName($formName);
		$this->clearFieldsets();
		$this->clearDeleteButtonText();
		$this->clearSubmitButtonText();

		// Check if database module is initialized
		// If no, throw error: Cannot use FormComponent without DatabaseModule
		if(!$GLOBALS['__SiteBuilder_ModuleManager']->isModuleInitialized(DatabaseModule::class)) {
			throw new ErrorException("The DatabaseModule must be initialized when using a FormComponent!");
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		$formDependencies = array(
				CSSDependency::init('Form/forms.css')
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

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		// Delete form
		if(isset($_POST['__SiteBuilder_DeleteForm_' . $this->formName])) {
			$this->delete();
		}

		// Proccess form
		if(isset($_POST['__SiteBuilder_SubmitForm_' . $this->formName])) {
			$this->process();
		}

		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
		$classes = 'sitebuilder-form';
		if(!empty($this->getHTMLClasses())) {
			$classes .= ' ' . $this->getHTMLClasses();
		}

		$html = '<form' . $id . ' class="' . $classes . '" method="POST" enctype="multipart/form-data"><table>';

		// Generate fieldset html
		$html .= '<tbody>';
		foreach($this->fieldsets as $fieldset) {
			$html .= $fieldset->getContent();
		}
		$html .= '</tbody>';

		// Generate submit and delete button html
		$html .= '<tfoot><tr>';

		if($this->showDelete) {
			$html .= '<td><input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_DeleteForm_' . $this->formName . '" value="' . $this->deleteButtonText . '"></td>';
			$html .= '<td>';
		} else {
			$html .= '<td colspan="2">';
		}
		$html .= '<input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_SubmitForm_' . $this->formName . '" value="' . $this->submitButtonText . '">';
		$html .= '</td>';

		$html .= '</tr></tfoot>';
		$html .= '</table></form>';

		return $html;
	}

	public function process(): void {
		// Get values from fieldsets that aren't manyField
		$values = array();
		foreach($this->fieldsets as $fieldset) {
			if(!($fieldset instanceof ManyFieldFormFieldset)) {
				$values = array_merge($values, $fieldset->process());
			}
		}

		$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();

		if($this->isNewObject) {
			// Create new object
			$objectID = $database->insert($this->mainTableDatabaseName, $values, $this->primaryKey);
			$this->setObjectID($objectID);
		} else {
			// Update old object
			$where = '`' . $this->primaryKey . "`='" . $this->objectID . "'";
			$database->update($this->mainTableDatabaseName, $values, $where);
		}

		// Proccess fieldsets that are manyField
		foreach($this->fieldsets as $fieldset) {
			if($fieldset instanceof ManyFieldFormFieldset) {
				$fieldset->process();
			}
		}
	}

	public function delete(): void {
		// Check if this form corresponds to a new entry in the database
		// If yes, throw error: Cannot delete a new form
		if($this->isNewObject) {
			throw new ErrorException("Cannot delete a form corresponding to a new database entry!");
		}

		// Delete entries in secondary tables
		foreach($this->fieldsets as $fieldset) {
			if($fieldset instanceof ManyFieldFormFieldset) {
				$fieldset->delete();
			}
		}

		// Delete entry in main table
		$database = $GLOBALS['__SiteBuilder_ModuleManager']->getModuleByClass(DatabaseModule::class)->db();
		$database->delete($this->mainTableDatabaseName, $this->primaryKey . "='" . $this->objectID . "'");
	}

	public function isNewObject(): bool {
		return $this->isNewObject;
	}

	public function setIsNewObject(bool $isNewObject, int $objectID = -1): self {
		$this->isNewObject = $isNewObject;

		if(!$this->isNewObject) {
			$this->setObjectID($objectID);
			$this->setShowDelete(true);
		} else {
			unset($this->objectID);
			$this->setShowDelete(false);
		}

		return $this;
	}

	public function getObjectID(): int {
		return $this->objectID;
	}

	private function setObjectID(int $objectID) {
		$this->objectID = $objectID;
	}

	public function getMainTableDatabaseName(): string {
		return $this->mainTableDatabaseName;
	}

	private function setMainTableDatabaseName(string $mainTableDatabaseName) {
		$this->mainTableDatabaseName = $mainTableDatabaseName;
	}

	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	public function setPrimaryKey(string $primaryKey): self {
		$this->primaryKey = $primaryKey;
		return $this;
	}

	public function clearPrimaryKey(): self {
		$this->setPrimaryKey('ID');
		return $this;
	}

	public function getFormName(): string {
		return $this->formName;
	}

	private function setFormName(string $formName) {
		$this->formName = $formName;
	}

	public function addFieldset(AbstractFormFieldset $fieldset): self {
		$fieldset->setParentForm($this);
		array_push($this->fieldsets, $fieldset);
		return $this;
	}

	public function getFieldsets(): array {
		return $this->fieldsets;
	}

	public function clearFieldsets(): self {
		$this->fieldsets = array();
		return $this;
	}

	public function getShowDelete(): bool {
		return $this->showDelete;
	}

	public function setShowDelete(bool $showDelete): self {
		$this->showDelete = $showDelete;
		return $this;
	}

	public function getDeleteButtonText(): string {
		return $this->deleteButtonText;
	}

	public function setDeleteButtonText(string $deleteButtonText): self {
		$this->deleteButtonText = $deleteButtonText;
		return $this;
	}

	public function clearDeleteButtonText(): self {
		$this->setDeleteButtonText('Delete');
		return $this;
	}

	public function getSubmitButtonText(): string {
		return $this->submitButtonText;
	}

	public function setSubmitButtonText(string $submitButtonText): self {
		$this->submitButtonText = $submitButtonText;
		return $this;
	}

	public function clearSubmitButtonText(): self {
		$this->setSubmitButtonText('Submit');
		return $this;
	}

}

