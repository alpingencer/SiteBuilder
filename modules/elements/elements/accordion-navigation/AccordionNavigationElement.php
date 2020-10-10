<?php

namespace SiteBuilder\Elements;

class AccordionNavigationElement extends Element {

	public static function generateHTMLFromPageInfo(array $pageInfo, string $currentPageInfoPath, string $currentPagePath = ''): string {
		$html = '';

		// If 'show-in-menu' is not true, skip
		if(isset($pageInfo['show-in-menu']) && $pageInfo['show-in-menu'] !== true) return $html;

		if(isset($pageInfo['children'])) {
			// Submenu
			$html .= '<li class="sitebuilder-has-submenu">';
			$html .= '<a href="javascript:void(0);">' . $pageInfo['title'] . '</a>';

			$html .= '<ul>';
			foreach($pageInfo['children'] as $childName => $child) {
				$html .= self::generateHTMLFromPageInfo($child, $currentPageInfoPath . '/' . $childName, $currentPagePath);
			}
			$html .= '</ul>';

			$html .= '</li>';
		} else {
			// Menu item
			// Set active
			if(!empty($currentPagePath) && $currentPageInfoPath === $currentPagePath) {
				$active = 'class="sitebuilder-submenu-active" ';
			} else {
				$active = '';
			}

			$html .= '<li>';
			$html .= '<a ' . $active . 'href="?p=' . $currentPageInfoPath . '">' . $pageInfo['title'] . '</a>';
			$html .= '</li>';
		}

		return $html;
	}

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		parent::__construct();
	}

	public function getDependencies(): array {
		return array(
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'jquery/jquery-3.5.1.min.js'),
				new Dependency(__SITEBUILDER_JS_DEPENDENCY, 'elements/accordion-navigation/accordion-navigation.js', 'defer')
		);
	}

	public function getContent(): string {
		$sb = $GLOBALS['__SiteBuilder_Core'];

		$html = '<nav class="sitebuilder-accordion-nav"><ul>';

		foreach($sb->getPageHierarchy()['children'] as $childName => $child) {
			$html .= self::generateHTMLFromPageInfo($child, $childName, $sb->getCurrentPage()->getPagePath());
		}

		$html .= '</ul></nav>';
		return $html;
	}

}
