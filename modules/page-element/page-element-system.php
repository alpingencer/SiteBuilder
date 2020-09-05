<?php

namespace SiteBuilder\PageElement;

use SiteBuilder\SiteBuilderFamily;
use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;

class PageElementSystem extends SiteBuilderSystem {

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireAll(PageElement::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		$elements = $page->getComponents(PageElement::class);

		// Sort elements by priority (lower means proccessed first)
		$elementsArray = array();
		foreach($elements as $element) {
			array_push($elementsArray, $element);
		}

		usort($elementsArray, function (PageElement $e1, PageElement $e2) {
			return $e1->getPriority() <=> $e2->getPriority();
		});

		// Proccess elements
		foreach($elementsArray as $element) {
			$page->body .= $element->getContent();
		}

		// Dependencies
		// Add all dependencies
		$dependencies = array();
		foreach($elementsArray as $element) {
			$dependencies = array_merge($dependencies, $element->getDependencies());
		}

		// Get rid of duplicate dependencies
		Dependency::removeDuplicates($dependencies);

		// Sort dependencies by type
		usort($dependencies, function (Dependency $d1, Dependency $d2) {
			return $d1->getType() <=> $d2->getType();
		});

		// Add dependencies to page
		$page->head .= '<!-- SiteBuilder Generated Dependencies -->';
		foreach($dependencies as $dependency) {
			$page->head .= $dependency->getHTML();
		}
		$page->head .= '<!-- End SiteBuilder Generated Dependencies -->';
	}

}
