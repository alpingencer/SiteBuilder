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
		// Get rid of duplicate dependencies
		$addedDependencies = array();
		$addedDependencySources = array();
		foreach($elementsArray as $element) {
			foreach($element->getDependencies() as $dependency) {
				if(in_array($dependency->getSource(), $addedDependencySources, true)) continue;

				array_push($addedDependencySources, $dependency->getSource());
				array_push($addedDependencies, $dependency);
			}
		}

		// Sort dependencies by type
		usort($addedDependencies, function (Dependency $d1, Dependency $d2) {
			return $d1->getType() <=> $d2->getType();
		});

		// Add dependencies to page
		foreach($addedDependencies as $dependency) {
			$normalizedPath = Dependency::getNormalizedPath($GLOBALS['SiteBuilder_Core']->getRootPath(), $dependency->getSource());
			$html = Dependency::getHTML($dependency->getType(), $normalizedPath, $dependency->getParams());
			$page->head .= $html;
		}
	}

}
