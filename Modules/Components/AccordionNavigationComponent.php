<?php

namespace SiteBuilder\Modules\Components;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\CM\Dependency\JSDependency;
use ErrorException;

/**
 * An AccordionNavigationComponent gets the websites page hierarchy from the WebsiteManager and
 * creates an accordion-style navigation menu according to it.
 * If you don't want a particular page to be displayed, set the page attribute 'show-in-menu' to
 * false.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components
 */
class AccordionNavigationComponent extends Component {

	/**
	 * Returns an instance of AccordionNavigationComponent
	 *
	 * @return AccordionNavigationComponent The initialized instance
	 */
	public static function init(): AccordionNavigationComponent {
		return new self();
	}

	/**
	 * Constructor for the AccordionNavigationComponent.
	 * To get an instance of this class, use AccordionNavigationComponent::init()
	 *
	 * @see AccordionNavigationComponent::init()
	 */
	private function __construct() {
		parent::__construct();

		// Check if website manager has been initialized
		// If no, throw error: AccordionNavigationComponent depends on the website manager
		if(!isset($GLOBALS['__SiteBuilder_WebsiteManager'])) {
			throw new ErrorException("AccordionNavigationComponent cannot be used if a WebsiteManager has not been initialized!");
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		return array(
				JSDependency::init('External/jQuery/jquery-3.5.1.min.js'),
				JSDependency::init('accordion-navigation.js', 'defer')
		);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];

		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
		$classes = 'sitebuilder-accordion';
		if(!empty($this->getHTMLClasses())) {
			$classes .= ' ' . $this->getHTMLClasses();
		}

		$html = '<nav' . $id . ' class="' . $classes . '"><ul>';

		foreach($wm->getHierarchy()->getAllPages()['children'] as $childName => $child) {
			$html .= $this->generateHTMLFromPages($child, $childName);
		}

		$html .= '</ul></nav>';
		return $html;
	}

	/**
	 * Generates an HTML unordered list (ul) according to the given pages array recursively.
	 *
	 * @param array $pages An array containing the page hierarchy
	 * @param string $currentPagesPath The current hierarchy path of the list
	 * @return string The generated HTML string
	 */
	private function generateHTMLFromPages(array $pages, string $currentPagesPath): string {
		$html = '';

		// If 'show-in-menu' is not true, skip
		if(isset($pages['show-in-menu']) && $pages['show-in-menu'] !== true) return $html;

		if(isset($pages['children'])) {
			// Submenu
			$html .= '<li class="sitebuilder-accordion--has-submenu">';
			$html .= '<a href="javascript:void(0);">' . $pages['title'] . '</a>';

			$html .= '<ul>';
			foreach($pages['children'] as $childName => $child) {
				$html .= $this->generateHTMLFromPages($child, $currentPagesPath . '/' . $childName);
			}
			$html .= '</ul>';

			$html .= '</li>';
		} else {
			// Menu item
			$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];

			// Set active
			if($currentPagesPath === $wm->getCurrentPagePath()) {
				$active = ' class="sitebuilder-accordion--active-submenu"';
			} else {
				$active = '';
			}

			$html .= '<li>';
			$html .= '<a' . $active . ' href="?p=' . $currentPagesPath . '">' . $pages['title'] . '</a>';
			$html .= '</li>';
		}

		return $html;
	}

}

