<?php

namespace SiteBuilder;

use ErrorException;
use Exception;
use SplObjectStorage;
use Throwable;

class Core {
	private $frameworkDirectory;
	private $pageContentDirectory;
	private $pageHierarchy;
	private $defaultPagePath;
	private $errorPagePaths;
	private $page;
	private $systems;

	public static function newInstance(array $pageHierarchy, string $defaultPagePath): self {
		return new self($pageHierarchy, $defaultPagePath);
	}

	public function __construct(array $pageHierarchy, string $defaultPagePath) {
		$GLOBALS['__SiteBuilder_Core'] = &$this;
		$this->setFrameworkDirectory('/sitebuilder/');
		$this->setPageContentDirectory('/content/');
		$this->setPageHierarchy($pageHierarchy);
		$this->setDefaultPagePath($defaultPagePath);
		$this->errorPagePaths = array();

		if(isset($_GET['p']) && !empty($_GET['p'])) {
			$this->setCurrentPage($_GET['p']);
		} else {
			// Redirect to set 'p' parameter in request URI
			$this->redirectToPage($this->defaultPagePath, true);
		}

		$this->systems = new SplObjectStorage();

		$this->setErrorHandler();
		$this->setHandlerToRedirectOnException();
	}

	public function run(): void {
		// Check if page exists in hierarchy
		// Else show 404 page
		if(!$this->isPageInHierarchy($this->page->getPagePath())) {
			if($this->isErrorPagePathDefined(404)) {
				$this->redirectToPage($this->getErrorPagePath(404));
			} else if($this->isErrorPagePathDefined(400)) {
				$this->redirectToPage($this->getErrorPagePath(400));
			} else {
				$this->showDefaultErrorPage(404);
				echo $this->page->getHTML();
				return;
			}
		}

		// Check if content files for the page, the global header and footer, and the page header and footer exist
		// Global header and footer path relative to page content directory
		// Page header and footer path relative to current page path
		$requirePaths = array();

		if(isset($this->pageHierarchy['global-header'])) array_push($requirePaths, $this->pageHierarchy['global-header']);
		if(isset($this->getPageInfoInHierarchy($this->page->getPagePath())['header'])) {
			array_push($requirePaths, dirname($this->page->getPagePath()) . '/' . $this->getPageInfoInHierarchy($this->page->getPagePath())['header']);
		}

		array_push($requirePaths, $this->page->getPagePath());

		if(isset($this->getPageInfoInHierarchy($this->page->getPagePath())['footer'])) {
			array_push($requirePaths, dirname($this->page->getPagePath()) . '/' . $this->getPageInfoInHierarchy($this->page->getPagePath())['footer']);
		}
		if(isset($this->pageHierarchy['global-footer'])) array_push($requirePaths, $this->pageHierarchy['global-footer']);


		foreach($requirePaths as $path) {
			if($this->contentFileForPathExists($path)) {
				// File found, do require
				require $this->getContentFileForPath($path);
			} else {
				// A required file was not found, show 501 page
				trigger_error("The path '" . $path . "' does not have a corresponding content file!", E_USER_WARNING);

				if($this->isErrorPagePathDefined(501)) {
					$this->redirectToPage($this->getErrorPagePath(501));
				} else if($this->isErrorPagePathDefined(500)) {
					$this->redirectToPage($this->getErrorPagePath(500));
				} else {
					$this->showDefaultErrorPage(501);
					return;
				}
			}
		}

		// Process systems
		foreach($this->systems as $system) {
			if($system->accepts($this->page)) {
				$system->process($this->page);
			}
		}

		// Show page
		echo $this->page->getHTML();

		// Restore old error handling
		$this->restoreDefaultExceptionHandler();
	}

	public function isPageInHierarchy(string $pagePath): bool {
		try {
			$this->getPageInfoInHierarchy($pagePath);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	public function getPageInfoInHierarchy(string $pagePath): array {
		// Normalize path
		$pagePath = normalizePathString($pagePath);

		// Start with root
		$currentPage = &$this->pageHierarchy;

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

	public function contentFileForPathExists(string $pagePath): bool {
		try {
			$this->getContentFileForPath($pagePath);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	public function getContentFileForPath(string $path): string {
		$path = normalizePathString($path);
		$contentFilePath = $_SERVER['DOCUMENT_ROOT'] . $this->pageContentDirectory . $path . '.php';

		if(!file_exists($contentFilePath)) {
			// File not found
			throw new ErrorException("The given path '$path' does not have a corresponding content file!");
		}

		return $contentFilePath;
	}

	public function setErrorPagePath(int $errorCode, string $pagePath): void {
		$pagePath = normalizePathString($pagePath);

		if(!$this->isPageInHierarchy($pagePath)) {
			// Error page not in hierarchy
			throw new ErrorException("The given error page path '$pagePath' is not in the page hierarchy!");
		} else if(!$this->contentFileForPathExists($pagePath)) {
			// Error page content not defined
			throw new ErrorException("The given error page path '$pagePath' does not have a corresponding content file!");
		} else {
			$this->errorPagePaths[$errorCode] = $pagePath;
		}
	}

	public function isErrorPagePathDefined(int $errorCode): bool {
		try {
			$this->getErrorPagePath($errorCode);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	public function getErrorPagePath(int $errorCode): string {
		if(isset($this->errorPagePaths[$errorCode])) {
			return $this->errorPagePaths[$errorCode];
		} else {
			throw new ErrorException("The page path for the error code '$errorCode' is not defined!");
		}
	}

	private function setErrorHandler(): void {
		set_error_handler(function (int $errorLevel, string $errorMessage, string $errorFile, int $errorLine) {
			// If error is a fatal error, throw an exception
			if($errorLevel === E_USER_ERROR) {
				throw new ErrorException($errorMessage);
			}

			// Create Exception to get debug trace
			$e = new Exception();

			switch($errorLevel) {
				case E_USER_WARNING:
					$errorType = 'PHP Warning';
					break;
				case E_USER_NOTICE:
					$errorType = 'PHP Notice';
					break;
				case E_USER_DEPRECATED:
					$errorType = 'PHP Deprecated warning';
					break;
			}

			$message = "$errorType: $errorMessage\nFile: $errorFile:$errorLine\nStack trace:\n" . $e->getTraceAsString();
			error_log($message);
		}, E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED);
	}

	public function restoreDefaultErrorHandler(): void {
		restore_error_handler();
	}

	private function setHandlerToRedirectOnException(): void {
		set_exception_handler(function (Throwable $e) {
			// Log exception
			error_log('Uncaught ' . $e->__toString());

			// Show error page
			if($this->isErrorPagePathDefined(500)) {
				$this->redirectToPage($this->getErrorPagePath(500));
			} else {
				$this->showDefaultErrorPage(500);
			}
		});
	}

	public function restoreDefaultExceptionHandler(): void {
		restore_exception_handler();
	}

	public function showDefaultErrorPage(int $errorCode): void {
		$this->page->clearContent();

		http_response_code($errorCode);

		$errorPage = getDefaultErrorPage($errorCode);
		$errorName = $errorPage['errorName'];
		$errorMessage = $errorPage['errorMessage'];

		$this->page->head = "<title>$errorCode $errorName</title>";
		$this->page->body = "<h1>$errorCode $errorName</h1><p>$errorMessage</p>";
		echo $this->page->getHTML();
		die();
	}

	public function redirectToPage(string $pagePath, bool $keepGETParams = false) {
		$pagePath = normalizePathString($pagePath);

		if($keepGETParams) {
			// Get HTTP query without 'p' parameter
			$params = http_build_query(array_diff_key($_GET, array('p' => '')));
		} else {
			$params = '';
		}

		if(!empty($params)) $params = '&' . $params;
		$uri = '?p=' . $pagePath . $params;

		// If redirecting to the same URI, show 508 page to avoid infinite redirecting
		if(getRequestURIWithoutGETArguments() . $uri === $_SERVER['REQUEST_URI']) {
			trigger_error('Infinite loop detected while redirecting! Showing the default error 508 page to avoid infinite redirecting.', E_USER_WARNING);
			$this->showDefaultErrorPage(508);
		}

		// Redirect and die
		header('Location:' . $uri, true, 303);
		die();
	}

	public function addSystem(System $system): void {
		$class = get_class($system);

		if($this->hasSystem($class)) {
			throw new ErrorException("A system of class '$class' has already been added!");
		} else {
			$this->systems->attach($system);
		}
	}

	public function addAllSystems(System ...$systems): void {
		foreach($systems as $system) {
			$this->addSystem($system);
		}
	}

	public function removeSystem(string $class): void {
		if($this->hasSystem($class)) {
			$this->systems->detach($this->getSystem($class));
		} else {
			trigger_error("The given system class name to remove '$class' was not found!", E_USER_NOTICE);
		}
	}

	public function removeAllSystem(string ...$classes): void {
		foreach($classes as $class) {
			$this->removeSystem($class);
		}
	}

	public function clearSystems(): void {
		$this->systems->removeAll($this->systems);
	}

	public function hasSystem(string $class): bool {
		try {
			$this->getSystem($class);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	public function getSystem(string $class): System {
		foreach($this->systems as $system) {
			if(get_class($system) === $class || is_subclass_of($system, $class)) {
				return $system;
			}
		}

		// No system found
		throw new ErrorException("The given system class name '$class' was not found!");
	}

	public function getAllSystems(): SplObjectStorage {
		return $this->systems;
	}

	public function setFrameworkDirectory(string $frameworkDirectory): void {
		$this->frameworkDirectory = normalizeDirectoryString($frameworkDirectory);
	}

	public function getFrameworkDirectory(): string {
		return $this->frameworkDirectory;
	}

	public function setPageContentDirectory(string $pageContentDirectory): void {
		$this->pageContentDirectory = normalizeDirectoryString($pageContentDirectory);
	}

	public function getPageContentDirectory(): string {
		return $this->pageContentDirectory;
	}

	private function setPageHierarchy(array $pageHierarchy): void {
		validatePageHierarchy($pageHierarchy);

		$this->pageHierarchy = $pageHierarchy;
		cascadePageAttributesDownInHierarchy($this->pageHierarchy);
	}

	public function getPageHierarchy(): array {
		return $this->pageHierarchy;
	}

	private function setDefaultPagePath(string $defaultPagePath): void {
		// Validate given default page path
		$defaultPagePath = normalizePathString($defaultPagePath);

		if(!$this->isPageInHierarchy($defaultPagePath)) {
			throw new ErrorException("The given default page path '$defaultPagePath' is not in the page hierarchy!");
		}

		$this->defaultPagePath = $defaultPagePath;
	}

	public function getDefaultPagePath(): string {
		return $this->defaultPagePath;
	}

	private function setCurrentPage(string $pagePath): void {
		if(empty($pagePath)) {
			throw new ErrorException("The given page path is empty!");
		}

		$p = normalizePathString($pagePath);

		if($p !== $pagePath) {
			// Redirect to normalize page path in request URI
			$this->redirectToPage($p, true);
		}

		$lastCharInURI = substr($_SERVER['REQUEST_URI'], -1);
		if($lastCharInURI === '?' || $lastCharInURI === '&') {
			// Redirect to remove trailing '?' or '&' in request URI
			$this->redirectToPage($p, true);
		}

		$this->page = new Page($p);
	}

	private function setCurrentPageToDefault(): void {
		$this->setCurrentPage($this->defaultPagePath);
	}

	public function getCurrentPage(): Page {
		return $this->page;
	}

}
