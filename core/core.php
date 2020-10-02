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
	 * The page hierarchy the framework manages
	 *
	 * @var array
	 */
	private $pageHierarchy;
	/**
	 * The root directory for the sitebuilder framework, relative to the server root
	 *
	 * @var string
	 */
	private $sitebuilderDirectoryPath;
	/**
	 * The root directory for the page content files, relative to the server root
	 *
	 * @var string
	 */
	private $pageContentDirectoryPath;
	/**
	 * The default page to show, determined by it's path in the page hierarchy
	 *
	 * @var string
	 */
	private $defaultPageHierarchyPath;
	/**
	 * The 404 page to show, determined by it's path in the page hierarchy
	 *
	 * @var string
	 */
	private $notFoundHierarchyPath;
	/**
	 * The 403 page to show, determined by it's path in the page hierarchy
	 *
	 * @var string
	 */
	private $forbiddenHierarchyPath;
	/**
	 * The current SiteBuilderPage instance
	 *
	 * @var SiteBuilderPage
	 */
	private $currentPage;
	/**
	 * The systems added to this core
	 *
	 * @var SplObjectStorage
	 */
	private $systems;

	/**
	 * Return an instance of SiteBuilderCore
	 *
	 * @return self The instantiated instance
	 * @see SiteBuilderCore::__construct()
	 */
	public static function newInstance(array $pageHierarchy, string $sitebuilderDirectoryPath, string $pageContentDirectoryPath): self {
		return new self($pageHierarchy, $sitebuilderDirectoryPath, $pageContentDirectoryPath);
	}

	/**
	 * Normalize a directory path such that it always starts and ends with a single slash character
	 *
	 * @param string The path to format
	 * @return string The formatted string
	 */
	public static function normalizeDirectoryPath(string $path): string {
		if(substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}

		if(substr($path, -1, 1) !== '/') {
			$path = $path . '/';
		}

		return $path;
	}

	/**
	 * Returns the absolute path of the page content files for the specified page hierarchy path
	 *
	 * @param string $pageContentDirectoryPath The root directory for the page content files,
	 *        relative to the server root
	 * @param string $pageHierarchyPath The page to find the file of
	 * @return string The computed file path
	 */
	public static function normalizePageHierarchyPath(string $pageContentDirectoryPath, string $pageHierarchyPath): string {
		return $_SERVER['DOCUMENT_ROOT'] . $pageContentDirectoryPath . $pageHierarchyPath . '.php';
	}


	/**
	 * Copies all attributes in a parent array down to it's children,
	 * unless the child defines it already
	 *
	 * @param array $pages The pages to cascade the attributes down of
	 */
	public static function cascadePageAttributesDownInHierarchy(array &$pages): void {
		if(!isset($pages['children'])) return;

		foreach(array_keys($pages) as $key) {
			if($key === 'children') continue;

			foreach($pages['children'] as &$child) {
				if(!isset($child[$key])) {
					$child[$key] = $pages[$key];
				}

				self::cascadePageAttributesDownInHierarchy($child);
			}
		}
	}

	/**
	 * Constructor for the core
	 *
	 * @param array $pageHierarchy The page hierarchy the framework manages
	 * @param string $sitebuilderDirectoryPath The root directory for the sitebuilder framework,
	 *        relative to the server root
	 * @param string $pageContentDirectoryPath The root directory for the page content files,
	 *        relative to the server root
	 * @param string $defaultPageHierarchyPath The default page to show, determined by it's path in the page hierarchy
	 */
	public function __construct(array $pageHierarchy, string $sitebuilderDirectoryPath, string $pageContentDirectoryPath, string $defaultPageHierarchyPath) {
		$this->pageHierarchy = $pageHierarchy;
		$this->sitebuilderDirectoryPath = self::normalizeDirectoryPath($sitebuilderDirectoryPath);
		$this->pageContentDirectoryPath = self::normalizeDirectoryPath($pageContentDirectoryPath);
		$this->defaultPageHierarchyPath = $defaultPageHierarchyPath;
		$this->notFoundHierarchyPath = '';
		$this->forbiddenHierarchyPath = '';
		$this->systems = new SplObjectStorage();

		if(isset($_GET['p'])) {
			$p = $_GET['p'];
		} else {
			$p = $defaultPageHierarchyPath;
		}

		$this->currentPage = new SiteBuilderPage($p);

		$GLOBALS['__SiteBuilderCore'] = &$this;
		self::cascadePageAttributesDownInHierarchy($this->pageHierarchy);
	}

	/**
	 * Gets a page in the hierarchy by a given page hierarchy path
	 * Note: This function returns a reference to the page.
	 *
	 * @param string $pageHierarchyPath The hierarchy path to search for
	 * @return array A reference to the array in the page hierarchy corresponding the the given hierarchy path
	 */
	public function &getPageInHierarchy(string $pageHierarchyPath) {
		// Start with root
		$currentPage = &$this->pageHierarchy;

		// Split page path into segments
		$segments = explode('/', $pageHierarchyPath);

		// Traverse page hierarchy and find current page
		foreach($segments as $segment) {
			if(isset($currentPage['children']) && isset($currentPage['children'][$segment])) {
				$currentPage = &$currentPage['children'][$segment];
			} else {
				$ret = null;
				return $ret;
			}
		}

		// Return the found page
		return $currentPage;
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
	 * Getter for the sitebuilder framework root path
	 *
	 * @return string
	 */
	public function getSiteBuilderDirectoryPath(): string {
		return $this->sitebuilderDirectoryPath;
	}

	/**
	 * Getter for the page content root path
	 *
	 * @return string
	 */
	public function getPageContentDirectoryPath(): string {
		return $this->pageContentDirectoryPath;
	}

	/**
	 * Getter for the default page hierarchy path
	 *
	 * @return string
	 */
	public function getDefaultPageHierarchyPath(): string {
		return $this->defaultPageHierarchyPath;
	}

	/**
	 * Sets the page hierarchy path of the 404 page to be shown if neccessary
	 *
	 * @param string $notFoundHierarchyPath The page hierarchy path to use
	 * @throws ErrorException If the page hierarchy path is not found in the page hierarchy,
	 *         or if the corresponding page content file is not found
	 * @return self Returns itself to chain other functions
	 */
	public function setNotFoundHierarchyPath(string $notFoundHierarchyPath): self {
		if(is_null($this->getPageInHierarchy($notFoundHierarchyPath))) {
			throw new ErrorException('The given 404 page path was not found in the page hierarchy!');
		} else if(!file_exists(self::normalizePageHierarchyPath($this->pageContentDirectoryPath, $notFoundHierarchyPath))) {
			throw new ErrorException('The corresponding file for the given 404 page path does not exist!');
		} else {
			$this->notFoundHierarchyPath = $notFoundHierarchyPath;
		}

		return $this;
	}

	/**
	 * Getter for the page hierarchy path of the 404 page
	 *
	 * @return string
	 */
	public function getNotFoundHierarchyPath(): string {
		return $this->notFoundHierarchyPath;
	}

	/**
	 * Sets the page hierarchy path of the 403 page to be shown if neccessary
	 *
	 * @param string $forbiddenHierarchyPath The page hierarchy path to use
	 * @throws ErrorException If the page hierarchy path is not found in the page hierarchy,
	 *         or if the corresnponding page content file is not found
	 * @return self Returns itself to chain other functions
	 */
	public function setForbiddenHierarchyPath(string $forbiddenHierarchyPath): self {
		if(is_null($this->getPageInHierarchy($forbiddenHierarchyPath))) {
			throw new ErrorException('The given 403 page path was not found in the page hierarchy!');
		} else if(!file_exists(self::normalizePageHierarchyPath($this->pageContentDirectoryPath, $forbiddenHierarchyPath))) {
			throw new ErrorException('The corresponding file for the given 403 page path does not exist!');
		} else {
			$this->forbiddenHierarchyPath = $forbiddenHierarchyPath;
		}

		return $this;
	}

	/**
	 * Getter for the page hierarchy path of the 403 page
	 *
	 * @return string
	 */
	public function getForbiddenHierarchyPath(): string {
		return $this->forbiddenHierarchyPath;
	}

	/**
	 * Getter for the current instance of SiteBuilderPage
	 *
	 * @return SiteBuilderPage
	 */
	public function getCurrentPage(): SiteBuilderPage {
		return $this->currentPage;
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
			$e = new Exception();
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
	 * Getter for the SplObjectStorage containing all added systems
	 *
	 * @return SplObjectStorage
	 */
	public function getAllSystems(): SplObjectStorage {
		return $this->systems;
	}

	/**
	 * Proccess all added systems and output the page to the browser
	 */
	public function run(): void {
		// Include current page
		$pageInHierarchy = $this->getPageInHierarchy($this->currentPage->getHierarchyPath());

		if(is_null($pageInHierarchy)) {
			if(empty($this->notFoundHierarchyPath)) {
				http_response_code(404);
				$this->currentPage->clearComponents();
				$this->currentPage->head = '<title>404 Not Found</title>';
				$this->currentPage->body = "<h1>404 Not Found</h1><p>The page you're looking was not found.</p>";
				echo $this->currentPage->getHTML();
				return;
			} else {
				header('Location:/?p=' . $this->notFoundHierarchyPath, true, 303);
			}
		}

		$includePath = self::normalizePageHierarchyPath($this->pageContentDirectoryPath, $this->currentPage->getHierarchyPath());

		if(file_exists($includePath)) {
			require $includePath;
		} else {
			throw new ErrorException('The corresponding file for the given page path was not found! File path: ' . $includePath);
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
			if($system->accepts($this->currentPage)) {
				$system->proccess($this->currentPage);
			}
		}

		// Show page
		echo $this->currentPage->getHTML();
	}

}
