<?php

namespace SiteBuilder;

use ErrorException;
use Exception;
use SplObjectStorage;

class SiteBuilderCore {
	private $pageHierarchy;
	private $sitebuilderDirectoryPath;
	private $pageContentDirectoryPath;
	private $defaultPageHierarchyPath;
	private $notFoundHierarchyPath;
	private $forbiddenHierarchyPath;
	private $currentPage;
	private $systems;

	public static function newInstance(array $pageHierarchy, string $sitebuilderDirectoryPath, string $pageContentDirectoryPath): self {
		return new self($pageHierarchy, $sitebuilderDirectoryPath, $pageContentDirectoryPath);
	}

	public static function normalizeDirectoryPath(string $path): string {
		if(substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}

		if(substr($path, -1, 1) !== '/') {
			$path = $path . '/';
		}

		return $path;
	}

	public static function normalizePageHierarchyPath(string $pageContentDirectoryPath, string $pageHierarchyPath): string {
		return $_SERVER['DOCUMENT_ROOT'] . $pageContentDirectoryPath . $pageHierarchyPath . '.php';
	}

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

	public function getPageHierarchy(): array {
		return $this->pageHierarchy;
	}

	public function getSiteBuilderDirectoryPath(): string {
		return $this->sitebuilderDirectoryPath;
	}

	public function getPageContentDirectoryPath(): string {
		return $this->pageContentDirectoryPath;
	}

	public function getDefaultPageHierarchyPath(): string {
		return $this->defaultPageHierarchyPath;
	}

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

	public function getNotFoundHierarchyPath(): string {
		return $this->notFoundHierarchyPath;
	}

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

	public function getForbiddenHierarchyPath(): string {
		return $this->forbiddenHierarchyPath;
	}

	public function getCurrentPage(): SiteBuilderPage {
		return $this->currentPage;
	}

	public function addSystem(SiteBuilderSystem $system): self {
		// Check if system of same class already exists
		if(!is_null($this->getSystem(get_class($system)))) {
			throw new ErrorException('Only one system of each class is allowed!');
		}

		$this->systems->attach($system);

		return $this;
	}

	public function addAllSystems(SiteBuilderSystem ...$systems): self {
		foreach($systems as $system) {
			$this->addSystem($system);
		}

		return $this;
	}

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

	public function removeAllSystems(string ...$classNames): self {
		foreach($classNames as $className) {
			$this->removeSystem($className);
		}

		return $this;
	}

	public function clearSystems(): self {
		$this->systems->removeAll($this->systems);
		return $this;
	}

	public function getSystem(string $className) {
		foreach($this->systems as $system) {
			if(get_class($system) === $className || is_subclass_of($system, $className)) {
				return $system;
			}
		}

		return null;
	}

	public function getAllSystems(): SplObjectStorage {
		return $this->systems;
	}

	public function run(): void {
		// Check if page is set
		if(!isset($this->currentPage)) {
			throw new ErrorException('The current page has not been set!');
		}

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
