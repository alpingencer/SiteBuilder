<?php

namespace SiteBuilder\Core\WM;

use ErrorException;

/**
 * A PageHierarchy is a multi-dimensional data structure used for defining every webpage the
 * WebsiteManager should know about.
 * It also stores additional metadata about the pages known as the page's "attributes".
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\WM
 */
class PageHierarchy {
	/**
	 * The associative array containing the entire hierarchy
	 *
	 * @var array
	 */
	private $pages;

	/**
	 * Returns an instance of PageHierarchy
	 *
	 * @param array $pages The associative array containing the hierarchy and the page metadata
	 * @return PageHierarchy The initialized instance
	 */
	public static function init(array $pages): PageHierarchy {
		return new self($pages);
	}

	/**
	 * Loads the hierarchy from a JSON file and returns an instance of PageHierarchy
	 *
	 * @param string $file The JSON file to read from
	 * @return PageHierarchy The initialized instance
	 */
	public static function loadFromJSON(string $file): PageHierarchy {
		return PageHierarchy::init(json_decode(file_get_contents($file), true));
	}

	/**
	 * Normalizes a page path string, parsing '.', '..' and '\\' strings and trimming slashes
	 *
	 * @param string $pagePath The path to process
	 * @return string The normalized path
	 */
	public static function normalizePathString(string $pagePath): string {
		$pagePath = str_replace('\\', '/', $pagePath);

		$parts = array_filter(explode('/', $pagePath), 'strlen');
		$absolutes = array();

		foreach($parts as $part) {
			if('.' === $part) continue;

			if('..' === $part) {
				array_pop($absolutes);
			} else {
				array_push($absolutes, $part);
			}
		}

		return implode('/', $absolutes);
	}

	/**
	 * Constructor for the PageHierarchy.
	 * To get an instance of this class, use PageHierarchy::init().
	 *
	 * @param $pages array The associative array containing the hierarchy and the page metadata
	 * @see PageHierarchy::init()
	 */
	private function __construct(array $pages) {
		$this->setPages($pages);
	}

	/**
	 * Recursive function for validating the associative array, such that each entry has a 'title'
	 * attribute defined
	 *
	 * @param array $pages The array to process
	 * @param string $currentPath The current page path being processed
	 * @return bool The boolean result of the validation
	 */
	private function validate(array $pages, string $currentPath = ''): bool {
		if(isset($pages['children'])) {
			// Site (page group)
			$requiredAttributes = array(
					'title'
			);

			// Check if attributes are set
			foreach($requiredAttributes as $requiredAttribute) {
				if(!isset($pages[$requiredAttribute])) {
					throw new ErrorException("Required attribute '$requiredAttribute' not set for page '$currentPath' in the given hierarchy!");
					return false;
				}
			}

			// Validate children
			foreach($pages['children'] as $childName => $child) {
				$this->validate($child, $currentPath . '/' . $childName);
			}
		} else {
			// Page
			$requiredAttributes = array(
					'title'
			);

			// Check if attributes are set
			foreach($requiredAttributes as $requiredAttribute) {
				if(!isset($pages[$requiredAttribute])) {
					throw new ErrorException("Required attribute '$requiredAttribute' not set for page '$currentPath' in the given hierarchy!");
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Cascades page attributes down in the hierarchy, such that an attribute defined in a parent
	 * page transfers to it's children if the children don't override it.
	 *
	 * @param array $pages
	 */
	private function cascadeDown(array &$pages): void {
		if(!isset($pages['children'])) return;

		// Loop attributes
		foreach(array_keys($pages) as $key) {
			if($key === 'children') continue;

			// Loop children
			foreach($pages['children'] as &$child) {
				if(!isset($child[$key])) {
					$child[$key] = $pages[$key];
				}

				$this->cascadeDown($child);
			}
		}
	}

	/**
	 * Check if a given page path is in the hierarchy
	 *
	 * @param string $pagePath The page path to search for
	 * @return bool The boolean result
	 */
	public function isPageDefined(string $pagePath): bool {
		try {
			$this->getPage($pagePath);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	/**
	 * Get an associative array with the page attributes from the hierarchy of a given page path
	 *
	 * @param string $pagePath The page path to search for
	 * @return array The associative array with the page metadata
	 */
	public function getPage(string $pagePath): array {
		// Normalize path
		$pagePath = PageHierarchy::normalizePathString($pagePath);

		// Start with root
		$currentPage = &$this->pages;

		// Split page path into segments
		$segments = explode('/', $pagePath);

		// Traverse page hierarchy and find current page
		foreach($segments as $segment) {
			if(isset($currentPage['children']) && isset($currentPage['children'][$segment])) {
				$currentPage = &$currentPage['children'][$segment];
			} else {
				throw new ErrorException("The given page path '$pagePath' was not found in the hierarchy!");
			}
		}

		// Return the found page
		return $currentPage;
	}

	/**
	 * Check if a given attribute is defined for a given page path
	 *
	 * @param string $pagePath The page path to check against
	 * @param string $attribute The attribute to check for
	 * @return bool The boolean result
	 */
	public function isPageAttributeDefined(string $pagePath, string $attribute): bool {
		return isset($this->getPage($pagePath)[$attribute]);
	}

	/**
	 * Get a given attribute of a given page path from the hierarchy
	 *
	 * @param string $pagePath The page path to get from
	 * @param string $attribute The attribute to get
	 * @return mixed The attribute metadata of the given page path
	 */
	public function getPageAttribute(string $pagePath, string $attribute) {
		// Check if page attribute is defined
		// If no, throw error: Cannot get undefined attribute
		if(!$this->isPageAttributeDefined($pagePath, $attribute)) {
			throw new ErrorException("The given attribute '$attribute' is not defined for the given page path '$pagePath'!");
		}

		return $this->getPage($pagePath)[$attribute];
	}

	/**
	 * Check if a given global attribute has been defined
	 *
	 * @param string $attribute The global attribute to check for
	 * @return bool The boolean result
	 */
	public function isGlobalAttributeDefined(string $attribute): bool {
		return isset($this->pages[$attribute]);
	}

	/**
	 * Get a given global attribute from the hierarchy
	 *
	 * @param string $attribute The attribute to get
	 * @return mixed The global metadata of the hierarchy
	 */
	public function getGlobalAttribute(string $attribute) {
		// Check if global attribute is defined
		// If no, throw error: Cannot get undefined attribute
		if(!$this->isGlobalAttributeDefined($attribute)) {
			throw new ErrorException("The given global attribute '$attribute' is not defined!");
		}

		return $this->pages[$attribute];
	}

	/**
	 * Getter for the hierarchy
	 *
	 * @return array The entire page hierarchy
	 */
	public function getAllPages(): array {
		return $this->pages;
	}

	/**
	 * Setter for the hierarchy
	 *
	 * @param array $pages
	 */
	private function setPages(array $pages): void {
		// Check if the given page hierarchy is valid
		// If no, throw error: Page hierarchy must be valid
		$valid = $this->validate($pages);
		if(!$valid) {
			throw new ErrorException("The given page hierarchy is invalid!");
		}

		$this->cascadeDown($pages);
		$this->pages = $pages;
	}

}

