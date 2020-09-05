<?php

namespace SiteBuilder\PageElement;

class AccordionNavigationMenuElement extends PageElement {
	private $showRoot;

	public static function newInstance(): self {
		return new self();
	}

	public static function generateHTMLFromArray(array $array): string {
		$html = '';

		// If 'show-in-menu' is not true, skip
		if(isset($array['show-in-menu']) && $array['show-in-menu'] !== true) return $html;

		if(isset($array['children'])) {
			// Submenu
			$html .= '<li class="sitebuilder-has-submenu">';
			$html .= '<a href="javascript:void(0);">' . $array['title'] . '</a>';

			$html .= '<ul>';
			foreach($array['children'] as $child) {
				$html .= self::generateHTMLFromArray($child);
			}
			$html .= '</ul>';

			$html .= '</li>';
		} else {
			// Menu item
			// Set active
			if(isset($array['active'])) {
				$active = 'class="sitebuilder-submenu-active" ';
			} else {
				$active = '';
			}
			$html .= '<li>';
			$html .= '<a ' . $active . 'href="' . $array['href'] . '">' . $array['title'] . '</a>';
			$html .= '</li>';
		}

		return $html;
	}

	public function __construct() {
		parent::__construct();
		$this->showRoot = true;
	}

	public function getDependencies(): array {
		return array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'jquery/jquery-3.5.1.min.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/accordion-nav-menu/accordion-nav-menu.js', 'defer')
		);
	}

	public function getContent(): string {
		$sb = $GLOBALS['__SiteBuilderCore'];
		$sb->getPageInHierarchy($sb->getCurrentPage()->getHierarchyPath())['active'] = true;
		$pageHierarchy = $sb->getPageHierarchy();

		$html = '<nav class="sitebuilder-accordion-nav"><ul>';

		if($this->showRoot) {
			$html .= self::generateHTMLFromArray($pageHierarchy);
		} else {
			foreach($pageHierarchy['children'] as $child) {
				$html .= self::generateHTMLFromArray($child);
			}
		}

		$html .= '</ul></nav>';

		return $html;
	}

	public function setShowRoot(bool $showRoot): self {
		$this->showRoot = $showRoot;
		return $this;
	}

	public function getShowRoot(): bool {
		return $this->showRoot;
	}

}
