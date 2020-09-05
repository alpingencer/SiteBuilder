<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;
use SiteBuilder\SiteBuilderFamily;

class FormSystem extends SiteBuilderSystem {

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireAll(FormElement::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		// Forms
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
		}
	}

}
