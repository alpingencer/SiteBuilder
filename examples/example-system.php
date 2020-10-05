<?php
use SiteBuilder\SiteBuilderFamily;
use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;

class ExampleSystem extends SiteBuilderSystem {

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireAll(ExampleComponent::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		$components = $page->getComponents(ExampleComponent::class);

		foreach($components as $component) {
			$html = '<p>Example Component [ myStringField= \'' . $component->getMyStringField() . '\'; myIntField= ' . strval($component->getMyIntField()) . ' ]</p>';
			$page->body .= $html;
		}
	}

}
