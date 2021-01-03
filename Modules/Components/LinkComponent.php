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
	 * Any additional $_GET parameters to add to the end of the URI
	 *
	 * @var string
	 */
	private $additionalGETParams;

	/**
	 * Returns an instance of LinkComponent
	 *
	 * @param string $linkPath The path to link to
	 * @return LinkComponent The initialized instance
	 */
	public static function init(string $linkPath): LinkComponent {
		return new self($linkPath);
	}

	/**
	 * Constructor for the LinkComponent.
	 * To get an instance of this class, use LinkComponent::init()
	 *
	 * @param string $linkPath The path to link to
	 * @see LinkComponent::init()
	 */
	protected function __construct(string $linkPath) {
		parent::__construct();

		// Check if website manager has been initialized
		// If no, throw error: LinkComponent depends on the website manager
		if(!isset($GLOBALS['__SiteBuilder_WebsiteManager'])) {
			throw new ErrorException("LinkComponent cannot be used if a WebsiteManager has not been initialized!");
		}

		$this->setLinkPath($linkPath);
		$this->clearInnerHTML();
		$this->clearAdditionalGETParams();
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
		if($this->linkPath !== '#') {
			$href = '?p=' . $href;
			if(!empty($this->additionalGETParams)) $href .= '&' . $this->additionalGETParams;
		}

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
	 * @see LinkComponent::$linkPath
	 */
	public function getLinkPath(): string {
		return $this->linkPath;
	}

	/**
	 * Setter for the link path
	 *
	 * @param string $linkPath
	 * @see LinkComponent::$linkPath
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
	 * @see LinkComponent::$isAbsolutePath
	 */
	public function isAbsolutePath(): bool {
		return $this->isAbsolutePath;
	}

	/**
	 * Setter for wether an absolute path was given
	 *
	 * @param bool $isAbsolutePath
	 * @see LinkComponent::$isAbsolutePath
	 */
	private function setIsAbsolutePath(bool $isAbsolutePath): void {
		$this->isAbsolutePath = $isAbsolutePath;
	}

	/**
	 * Getter for the inner HTML
	 *
	 * @return string
	 * @see LinkComponent::$innerHTML
	 */
	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

	/**
	 * Setter for the inner HTML
	 *
	 * @param string $innerHTML
	 * @return self Returns itself for chaining other functions
	 * @see LinkComponent::$innerHTML
	 */
	public function setInnerHTML(string $innerHTML): self {
		$this->innerHTML = $innerHTML;
		return $this;
	}

	/**
	 * Clears the inner HTML
	 *
	 * @return self Returns itself for chaining other functions
	 * @see LinkComponent::$innerHTML
	 */
	public function clearInnerHTML(): self {
		$this->setInnerHTML('');
		return $this;
	}

	/*
	 * Getter for the additional $_GET params
	 * @return string
	 * @see LinkComponent::$additionalGETParams
	 */
	public function getAdditionalGETParams(): string {
		return $this->additionalGETParams;
	}

	/**
	 * Setter for the additional $_GET params
	 *
	 * @param string $innerHTML
	 * @return self Returns itself for chaining other functions
	 * @see LinkComponent::$additionalGETParams
	 */
	public function setAdditionalGETParams(string $additionalGETParams): self {
		$this->additionalGETParams = trim($additionalGETParams, '&');
		return $this;
	}

	/**
	 * Clears the additional $_GET params
	 *
	 * @return self Returns itself for chaining other functions
	 * @see LinkComponent::$additionalGETParams
	 */
	public function clearAdditionalGETParams(): self {
		$this->setAdditionalGETParams('');
		return $this;
	}

}

