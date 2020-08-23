<?php

namespace SiteBuilder\PageElement;

class AccordionNavigationMenuWidget extends JavascriptWidget {
	private $showRoot;

	public function __construct() {
		$dependencies = array(
				new Dependency(SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/external-resources/jquery/jquery-3.5.1.min.js'),
				new Dependency(SITEBUILDER_JS_DEPENDENCY, 'javascript-widgets/widgets/accordion-nav-menu/accordion-nav-menu.js', 'defer')
		);
		parent::__construct($dependencies);
		$this->showRoot = true;
	}

	public static function newInstance(): self {
		return new self();
	}

	public function getPages(): array {
		return $this->pages;
	}

	public function setShowRoot(bool $showRoot): self {
		$this->showRoot = $showRoot;
		return $this;
	}

	public function getShowRoot(): bool {
		return $this->showRoot;
	}

	public function getContent(): string {
		$sb = $GLOBALS['SiteBuilder_Core'];
		$sb->setPageAttributeInHierarchy($sb->page->getPath(), 'active', true);
		$pages = $sb->getPageHierarchy();

		$html = '<nav class="sitebuilder-accordion-nav"><ul>';

		if($this->showRoot) {
			$html .= self::generateHTMLFromArray($pages);
		} else {
			foreach($pages['children'] as $child) {
				$html .= self::generateHTMLFromArray($child);
			}
		}

		$html .= '</ul></nav>';

		return $html;
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

}
