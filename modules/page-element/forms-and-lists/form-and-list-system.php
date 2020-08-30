<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;
use SiteBuilder\SiteBuilderFamily;
use SiteBuilder\Database\DatabaseComponent;

class FormAndListSystem extends SiteBuilderSystem {

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireAll(DatabaseComponent::class)->requireOne(FormElement::class, ListElement::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		$database = $page->getComponent(DatabaseComponent::class);

		// Forms
		if($page->hasComponent(FormElement::class)) {
			$elements = $page->getComponents(FormElement::class);
			foreach($elements as $element) {
				// Delete form
				if(isset($_POST['__SiteBuilder_DeleteForm'])) {
					$element->getDeleteFunction()();
				}

				// Proccess form
				if(isset($_POST['__SiteBuilder_SubmitForm'])) {
					$element->getProccessFunction()();
				}

				$element->html .= '<form method="POST" enctype="multipart/form-data"><table class="sitebuilder-form-table">';

				// Generate fieldset html
				foreach($element->getFieldsets() as $fieldset) {
					$element->html .= '<tr><td>' . $fieldset->getPrompt() . ':</td>';

					if($fieldset->isManyField()) {
						$minNumFields = ($fieldset->getMinNumFields() !== 0) ? ' data-min-fields="' . $fieldset->getMinNumFields() . '"' : '';
						$maxNumFields = ($fieldset->getMaxNumFields() !== 0) ? ' data-max-fields="' . $fieldset->getMaxNumFields() . '"' : '';

						$element->html .= '<td class="sitebuilder-many-fields"' . $minNumFields . $maxNumFields . '>';
						$element->html .= '<fieldset class="sitebuilder-template-fieldset">';
					} else {
						$element->html .= '<td>';
						$element->html .= '<fieldset>';
					}

					foreach($fieldset->getFields() as $field) {
						$element->html .= $field->getInnerHTML();
					}

					$element->html .= '</fieldset></td></tr>';
				}

				// Generate submit button html
				$element->html .= '<tr>';

				if($element->getShowDelete()) {
					$element->html .= '<td><input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_DeleteForm" value="' . $element->getDeleteText() . '"></td>';
					$element->html .= '<td>';
				} else {
					$element->html .= '<td colspan="2">';
				}
				$element->html .= '<input class="sitebuilder-form-button" type="submit" name="__SiteBuilder_SubmitForm" value="' . $element->getSubmitText() . '">';

				$element->html .= '</td></tr>';
				$element->html .= '</table></form>';
			}
		}

		// Lists
		if($page->hasComponent(ListElement::class)) {
			$elements = $page->getComponents(ListElement::class);
			foreach($elements as $element) {
				// Query database
				$query = 'SELECT ' . $element->getIDDatabaseName() . ', ' . implode(', ', $element->getColumnDatabaseNames());
				$query .= ' FROM ' . $element->getTableDatabaseName();
				$query .= ' WHERE ' . $element->getQueryCriteria();
				$query .= ' ORDER BY ' . $element->getDefaultSort();
				$result = $database->query($query);

				// Set table id
				$tableID = $element->getTableID();

				// Set table classes
				$tableClasses = array(
						'sitebuilder-list-table'
				);
				if(!empty($element->getRowOnClickRef())) {
					array_push($tableClasses, 'sitebuilder-hover-table');
				}

				// Set columns
				$columns = array();
				for($i = 0; $i < count($element->getColumnNames()) + 1; $i++) {
					// If not show id, skip
					if($i === 0 && !$element->getShowID()) continue;

					if($i === 0) {
						$column = $element->getIDColumnName();
					} else {
						$column = $element->getColumnNames()[$i - 1];
					}

					array_push($columns, $column);
				}

				// Set rows
				$rows = array();
				foreach($result as $res) {
					// Set row onclick attribute
					if(empty($element->getRowOnClickRef())) {
						$onClick = '';
					} else {
						$id = $res[0];
						// Check if on click ref has other get parameters
						if(strpos($element->getRowOnClickRef(), '?') !== false) {
							$onClick = 'window.location.href=\'' . $element->getRowOnClickRef() . '&amp;id=' . $id . '\'';
						} else {
							$onClick = 'window.location.href=\'' . $element->getRowOnClickRef() . '?id=' . $id . '\'';
						}
					}

					// Set cells
					$cells = array();
					for($i = 0; $i < count($element->getColumnNames()) + 1; $i++) {
						// If not show id, skip
						if($i === 0 && !$element->getShowID()) continue;

						$cell = SortableTableCell::newInstance($res[$i]);
						array_push($cells, $cell);
					}

					$row = SortableTableRow::newInstance()->setOnClick($onClick)->setCells($cells);
					array_push($rows, $row);
				}

				// Remove the list element and add a sortable table widget in its place
				$page->removeComponent($element);
				$page->addComponent(SortableTableWidget::newInstance($columns)->setPriority($element->getPriority())->setTableID($tableID)->setTableClasses($tableClasses)->setRows($rows));
			}
		}
	}

}
