<?php

namespace SiteBuilder;

use ErrorException;
use Exception;
use SplObjectStorage;

/**
 * The core class for the SiteBuilder framework.<br>
 * To use this framework, follow these basic steps:<br>
 * <ol>
 * <li>Initialize a SiteBuilderCore object, giving it a page hierarchy to manage</li>
 * <li>Add SiteBuilderSystems to the core</li>
 * <li>Initialize a SiteBuilderPage object, giving it the current path in the page hierarchy</li>
 * <li>Manipulate the head content of the page directly using the $page->head public field</li>
 * <li>Manipulate the body content of the page indirectly using SiteBuilderComponents</li>
 * <li>Set the $page field of the core object</li>
 * <li>Run the core</li>
 * </ol>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see SiteBuilderPage
 * @see SiteBuilderComponent
 * @see SiteBuilderSystem
 */
class SiteBuilderCore {
	/**
	 * The systems added to this core
	 *
	 * @var SplObjectStorage
	 */
	private $systems;
	/**
	 * The current page
	 *
	 * @var SiteBuilderPage
	 */
	public $page;
	/**
	 * The root directory path for the sitebuilder framework, relative to the server root
	 *
	 * @var string
	 */
	private $rootPath;
	/**
	 * The associative array containing all of the pages SiteBuilder should manage
	 *
	 * @var array
	 */
	private $pageHierarchy;

	/**
	 * Constructor for the core
	 *
	 * @param string $rootPath The root directory path for the sitebuilder framework, relative to the server root
	 */
	public function __construct(string $rootPath, array $pageHierarchy) {
		// Set SiteBuilderCore global
		$GLOBALS['SiteBuilder_Core'] = $this;

		$this->systems = new SplObjectStorage();

		$this->rootPath = $rootPath;

		// Normalize root path to always start and end with '/'
		if(substr($this->rootPath, 0, 1) !== '/') {
			$this->rootPath = '/' . $this->rootPath;
		}

		if(substr($this->rootPath, -1, 1) !== '/') {
			$this->rootPath = $this->rootPath . '/';
		}

		$this->pageHierarchy = $pageHierarchy;

		// 'Cascade' attributes down into children if not overridden
		self::cascadePageAttributesDownInHierarchy($this->pageHierarchy);
	}

	/**
	 * Add a system to this core
	 *
	 * @param SiteBuilderSystem $system The system to be added
	 * @return self Returns itself to chain other functions
	 * @throws ErrorException If a system of the same class or subclass already exists
	 */
	public function addSystem(SiteBuilderSystem $system): self {
		// Check if system of same class already exists
		if(!is_null($this->getSystem(get_class($system)))) {
			throw new ErrorException('Only one system of each class is allowed!');
		}

		$this->systems->attach($system);

		return $this;
	}

	/**
	 * Add all the given systems to this core
	 *
	 * @param SiteBuilderSystem ...$systems The systems to be added
	 * @return self Returns itself to chain other functions
	 */
	public function addAllSystems(SiteBuilderSystem ...$systems): self {
		foreach($systems as $system) {
			$this->addSystem($system);
		}

		return $this;
	}

	/**
	 * Remove a system from this core
	 *
	 * @param string $className The class name of the system to be removed
	 * @return self Returns itself to chain other functions
	 */
	public function removeSystem(string $className): self {
		$system = $this->getSystem($className);

		// Check if the system has been added to the core
		if(is_null($system)) {
			// Create a new exception to get the call trace
			$e = new Exception();

			// Trigger a PHP notice and print the call trace
			trigger_error("The given system class name was not found!\nStack trace:\n" . $e->getTraceAsString(), E_USER_NOTICE);
		} else {
			$this->systems->detach($system);
		}

		return $this;
	}

	/**
	 * Remove all the given systems from this core
	 *
	 * @param SiteBuilderSystem ...$systems The class names of the systems to be removed
	 * @return self Returns itself to chain other functions
	 */
	public function removeAllSystems(string ...$classNames): self {
		foreach($classNames as $className) {
			$this->removeSystem($className);
		}

		return $this;
	}

	/**
	 * Remove all the systems added to this core
	 *
	 * @return self Returns itself to chain other functions
	 */
	public function clearSystems(): self {
		$this->systems->removeAll($this->systems);
		return $this;
	}

	/**
	 * Get a system added to this core by its class name
	 *
	 * @param string $className The class name to be searched for.
	 *        Note the given class name can also be the name of the parent class of the system.
	 * @return SiteBuilderSystem|NULL The system if it's found, null otherwise
	 */
	public function getSystem(string $className) {
		foreach($this->systems as $system) {
			if(get_class($system) === $className || is_subclass_of($system, $className)) {
				return $system;
			}
		}

		return null;
	}

	/**
	 * Getter for the root path
	 *
	 * @return string
	 */
	public function getRootPath(): string {
		return $this->rootPath;
	}

	/**
	 * Getter for the page hierarchy
	 *
	 * @return array
	 */
	public function getPageHierarchy(): array {
		return $this->pageHierarchy;
	}

	/**
	 * Gets a page in the hierarchy by a given $pagePath.
	 * Note: This function returns a reference to the page.
	 *
	 * @param string $pagePath The path to search for
	 * @return array A reference to the array in the page hierarchy corresponding the the given path
	 */
	public function &getPageInHierarchy(string $pagePath): array {
		// Start with root
		$currentArray = &$this->pageHierarchy;

		// Split page path
		$segments = explode('/', $pagePath);

		// Traverse pages and find current page
		foreach($segments as $key => $segment) {
			if(isset($currentArray['children']) && isset($currentArray['children'][$segment])) {
				$currentArray = &$currentArray['children'][$segment];
			} else if($key !== array_key_last($segments)) {
				// Create a new exception to get the call trace
				$e = new Exception();

				// Trigger a PHP notice and print the call trace
				$message = "The given page path was not found in the given page hierarchy!\nStack trace:\n";
				trigger_error($message . $e->getTraceAsString(), E_USER_WARNING);

				// Return an empty array on malformed page path
				return array();
			}
		}

		// Return the found page
		return $currentArray;
	}

	public function setPageAttributeInHierarchy(string $pagePath, string $key, $value): self {
		$this->getPageInHierarchy($pagePath)[$key] = $value;
		return $this;
	}

	/**
	 * Proccess all added systems and output the page to the browser
	 *
	 * @throws ErrorException If the page has not yet been set
	 */
	public function run(): void {
		// Check if page is set
		if(!isset($this->page)) {
			throw new ErrorException('$page has not been set!');
		}

		// Sort systems by priority (lower means proccessed first)
		$systemsArray = array();
		foreach($this->systems as $system) {
			array_push($systemsArray, $system);
		}

		usort($systemsArray, function (SiteBuilderSystem $s1, SiteBuilderSystem $s2) {
			return $s1->getPriority() <=> $s2->getPriority();
		});

		// Proccess all systems
		foreach($systemsArray as $system) {
			if($system->accepts($this->page)) {
				$system->proccess($this->page);
			}
		}

		// Show page
		echo $this->page->getHTML();
	}

	/**
	 * Copies all attributes in a parent array down to it's children,
	 * unless the child defines it already
	 *
	 * @param array $page The page to cascade the attributes down of
	 */
	public static function cascadePageAttributesDownInHierarchy(array &$page) {
		if(!isset($page['children'])) return;

		foreach(array_keys($page) as $key) {
			if($key === 'children') continue;

			foreach($page['children'] as &$child) {
				if(!isset($child[$key])) {
					$child[$key] = $page[$key];
				}

				self::cascadePageAttributesDownInHierarchy($child);
			}
		}
	}

}
