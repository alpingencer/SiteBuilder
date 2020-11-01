<?php

namespace SiteBuilder\Modules\Components;

use SiteBuilder\Core\CM\Component;
use SiteBuilder\Core\WM\PageHierarchy;
use ErrorException;

/**
 * A LinkComponent provides a way to quickly link to a different page on your website.
 * You can either give a relative path, which will search for and match the closest path in the
 * hierarchy, or an absolute path, which will immediately search starting from the root.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Components
 */
class LinkComponent extends Component {
	/**
	 * The path string of the link
	 *
	 * @var string
	 */
	private $linkPath;
	/**
	 * Wether the given path is an absolute path
	 *
	 * @var bool
	 */
	private $isAbsolutePath;
	/**
	 * If not empty, a custom inner HTML will be displayed instead of the page title
	 *
	 * @var string
	 */
	private $innerHTML;

	/**
	 * Returns an instance of LinkComponent
	 *
	 * @param string $linkPath The path to link to
	 * @param string $innerHTML The inner content of the HTML anchor tag
	 * @return LinkComponent The initialized instance
	 */
	public static function init(string $linkPath, string $innerHTML = ''): LinkComponent {
		return new self($linkPath, $innerHTML);
	}

	/**
	 * Constructor for the LinkComponent.
	 * To get an instance of this class, use LinkComponent::init()
	 *
	 * @param string $linkPath The path to link to
	 * @param string $innerHTML The inner content of the HTML anchor tag
	 * @see LinkComponent::init()
	 */
	protected function __construct(string $linkPath, string $innerHTML) {
		parent::__construct();

		// Check if website manager has been initialized
		// If no, throw error: LinkComponent depends on the website manager
		if(!isset($GLOBALS['__SiteBuilder_WebsiteManager'])) {
			throw new ErrorException("LinkComponent cannot be used if a WebsiteManager has not been initialized!");
		}

		$this->setLinkPath($linkPath);
		$this->setInnerHTML($innerHTML);
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getDependencies()
	 */
	public function getDependencies(): array {
		return array();
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Component::getContent()
	 */
	public function getContent(): string {
		// Set inner HTML
		$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];
		if(empty($this->innerHTML) && $this->linkPath !== '#') {
			$innerHTML = $wm->getHierarchy()->getPageAttribute($this->linkPath, 'title');
		} else if($this->linkPath === '#') {
			$innerHTML = 'Broken link';
		} else {
			$innerHTML = $this->innerHTML;
		}

		// Set href
		$href = $this->linkPath;
		if($this->linkPath !== '#') $href = '?p=' . $href;

		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
		if(empty($this->getHTMLClasses())) {
			$classes = '';
		} else {
			$classes = ' class="' . $this->getHTMLClasses() . '"';
		}

		return '<a' . $id . $classes . ' href="' . $href . '">' . $innerHTML . '</a>';
	}

	/**
	 * Getter for the link path
	 *
	 * @return string
	 */
	public function getLinkPath(): string {
		return $this->linkPath;
	}

	/**
	 * Setter for the link path
	 *
	 * @param string $linkPath
	 */
	private function setLinkPath(string $linkPath): void {
		$this->setIsAbsolutePath(substr($linkPath, 0, 1) === '/');
		$linkPath = PageHierarchy::normalizePathString($linkPath);

		$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];
		$currentPagePath = $wm->getCurrentPagePath();

		if($this->isAbsolutePath || dirname($currentPagePath) === '.') {
			// Absolute path given or current page is top-level
			if($wm->getHierarchy()->isPageDefined($linkPath)) {
				// Page found
				$this->linkPath = $linkPath;
			} else {
				// Page not found
				$this->linkPath = '#';
				trigger_error("The given link path '/$linkPath' was not found in the page hierarchy!", E_USER_WARNING);
			}
		} else {
			// Current page is not top-level
			// Search one directory higher up until page is found
			do {
				$dirname = dirname($currentPagePath);

				if($dirname === '.') {
					$dirname = '';
				} else {
					$dirname .= '/';
				}

				// Search one directory higher
				$searchHierarchyPath = $dirname . $linkPath;

				if($wm->getHierarchy()->isPageDefined($searchHierarchyPath)) {
					// Link path found
					$this->linkPath = $searchHierarchyPath;
				} else if($dirname === '') {
					// Link path not found
					$this->linkPath = '#';
					trigger_error("The given link path '$linkPath' was not found in the page hierarchy!", E_USER_WARNING);
				}

				$currentPagePath = $dirname;
			} while(!isset($this->linkPath));
		}
	}

	/**
	 * Getter for wether an absolute path was given
	 *
	 * @return bool
	 */
	public function isAbsolutePath(): bool {
		return substr($this->linkPath, 0, 1) === '/';
	}

	/**
	 * Setter for wether an absolute path was given
	 *
	 * @param bool $isAbsolutePath
	 */
	private function setIsAbsolutePath(bool $isAbsolutePath): void {
		$this->isAbsolutePath = $isAbsolutePath;
	}

	/**
	 * Getter for the inner HTML
	 *
	 * @return string
	 */
	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

	/**
	 * Setter for the inner HTML
	 *
	 * @param string $innerHTML
	 */
	private function setInnerHTML(string $innerHTML): void {
		$this->innerHTML = $innerHTML;
	}

}

