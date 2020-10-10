<?php

namespace SiteBuilder\Elements;

class FormElement extends Element {
	private $fieldsets;
	private $formName;
	private $deleteText, $submitText;
	private $showDelete;
	private $autoProccess, $autoDeleteDatabaseEntry;

	public static function newInstance(string $formName): self {
		return new self($formName);
	}

	public function __construct(string $formName) {
		parent::__construct();
		$this->fieldsets = array();
		$this->formName = $formName;
		$this->deleteText = 'Delete';
		$this->submitText = 'Submit';
		$this->showDelete = true;
		$this->autoProccess = true;
		$this->autoDeleteDatabaseEntry = true;
	}

	public function getDependencies(): array {
		$coreDependencies = array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'jquery/jquery-3.5.1.min.js'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/form/forms.css'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/form/many-fields.js', 'defer'),
				new Dependency(__SITEBUILDER_CSS_DEPENDENCY, 'elements/form/many-fields.css')
		);

		if(!(isset($this) && get_class($this) == __CLASS__)) {
			return $coreDependencies;
		} else {
			$formFieldDependencies = array();

			foreach($this->fieldsets as $fieldset) {
				foreach($fieldset->getFields() as $field) {
					$formFieldDependencies = array_merge($formFieldDependencies, $field->getDependencies());
				}
			}

			$dependencies = array_merge($coreDependencies, $formFieldDependencies);
			Dependency::removeDuplicates($dependencies);
			return $dependencies;
		}
	}

	public function getContent(): string {
		// Proccess form
		if($this->autoProccess && isset($_POST['__SiteBuilder_SubmitForm_' . $this->formName])) {
			$this->proccess();
		}

		// Delete form
		if($this->autoDeleteDatabaseEntry && isset($_POST['__SiteBuilder_DeleteForm_' . $this->formName])) {
			$this->deleteDatabaseEntry();
		}

		$html = '<form method="POST" enctype="multipart/form-data"><table class="sitebuilder-form-table">';

		// Generate fieldset html
		$html .= '<tbody>';

		foreach($this->fieldsets as $fieldset) {
			$html .= '<tr><td>' . $fieldset->getPrompt() . ':</td>';

			if($fieldset->isManyField()) {
				$minNumFields = ($fieldset->getMinNumFields() !== 0) ? ' data-min-fields="' . $fieldset->getMinNumFields() . '"' : '';
				$maxNumFields = ($fieldset->getMaxNumFields() !== 0) ? ' data-max-fields="' . $fieldset->getMaxNumFields() . '"' : '';

				$html .= '<td class="sitebuilder-many-fields"' . $minNumFields . $maxNumFields . '>';
				$html .= '<fieldset class="sitebuilder-template-fieldset">';
			} else {
				$html .= '<td>';
				$html .= '<fieldset>';
			}

			foreach($fieldset->getFields() as $field) {
				$html .= $field->getContent();
			}

			$html .= '</fieldset></td></tr>';
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

		$html .= '</td></tr></tfoot>';
		$html .= '</table></form>';

		return $html;
	}

	public function proccess(): self {
		// TODO Proccess form
		return $this;
	}

	public function deleteDatabaseEntry(): self {
		// TODO Delete form
		return $this;
	}

	public function addFieldset(FormFieldset $fieldset): self {
		array_push($this->fieldsets, $fieldset);
		return $this;
	}

	public function getFieldsets(): array {
		return $this->fieldsets;
	}

	public function getFormName(): string {
		return $this->formName;
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

	public function setAutoProccess(bool $autoProccess): self {
		$this->autoProccess = $autoProccess;
		return $this;
	}

	public function getAutoProccess(): bool {
		return $this->autoProccess;
	}

	public function setAutoDeleteDatabaseEntry(bool $autoDeleteDatabaseEntry): self {
		$this->autoDeleteDatabaseEntry = $autoDeleteDatabaseEntry;
		return $this;
	}

	public function getAutoDeleteDatabaseEntry(): bool {
		return $this->autoDeleteDatabaseEntry;
	}

}
