<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Family;
use SiteBuilder\Page;
use SiteBuilder\System;

class ElementSystem extends System {

	public function __construct() {
		parent::__construct(Family::newInstance()->requireAll(Element::class));
	}

	public function process(Page $page): void {
		$elements = $page->getAllComponentsByClass(Element::class);

		// Process elements
		foreach($elements as $element) {
			$page->body .= $element->getContent();
		}

		// Dependencies
		// Add all dependencies
		$dependencies = array();
		foreach($elements as $element) {
			$dependencies = array_merge($dependencies, $element->getDependencies());
		}

		// Get rid of duplicate dependencies
		Dependency::removeDuplicates($dependencies);

		// Sort dependencies by type
		usort($dependencies, function (Dependency $d1, Dependency $d2) {
			return $d1->getType() <=> $d2->getType();
		});

		// Add dependencies to page
		if(!empty($dependencies)) {
			$dependencyHTML = '<!-- SiteBuilder Generated Dependencies -->';
			foreach($dependencies as $dependency) {
				$dependencyHTML .= $dependency->getHTML();
			}
			$dependencyHTML .= '<!-- End SiteBuilder Generated Dependencies -->';
			$page->head .= $dependencyHTML;
		}
	}

}
