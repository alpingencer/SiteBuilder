<?php

namespace SiteBuilder\PageElement;

class FormElement extends PageElement {
	private $fieldsets;
	private $deleteText, $submitText;
	private $showDelete;
	private $proccessFunction, $deleteFunction;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		parent::__construct();
		$this->fieldsets = array();
		$this->deleteText = 'Delete';
		$this->submitText = 'Submit';
		$this->showDelete = true;
		$this->proccessFunction = function () {};
		$this->deleteFunction = function () {};
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
		$html = '<form method="POST" enctype="multipart/form-data"><table class="sitebuilder-form-table">';

		// Generate fieldset html
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

		// Generate submit button html
		$html .= '<tr>';

		if($this->showDelete) {
			$html .= '<td><input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_DeleteForm" value="' . $this->deleteText . '"></td>';
			$html .= '<td>';
		} else {
			$html .= '<td colspan="2">';
		}
		$html .= '<input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_SubmitForm" value="' . $this->submitText . '">';

		$html .= '</td></tr>';
		$html .= '</table></form>';

		return $html;
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
