<?php

namespace SiteBuilder\Core\WM;

use ErrorException;
use Throwable;

/**
 * <p>
 * The website management of SiteBuilder is a content management system (CMS) that handles the
 * inclusion of the PHP content, header and footer files of all of the websites webpages, and
 * provides a convenient way to store metadata about the pages themselves.
 * </p>
 * <p>
 * To use the CMS, initialize an instace of this class using WebsiteManager::init(), passing in an
 * instance of a PageHierarchy in the configuration parameters, and call its run() method. The
 * WebsiteManager will automatically find the necessary things it needs to manage your website
 * (which is of course also configureable). In addition, the WebsiteManager will also automatically
 * use the 'p' GET parameter from the URL to determine which page should be shown. As such, you only
 * need to define one 'index.php' for each website you have on your server. The rest of the
 * page-specific components and scripts should go into the corresponding content files.
 * </p>
 * <p>
 * Note that WebsiteManager is a Singleton class, meaning only one instance of it can be initialized
 * at
 * a time.
 * </p>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\WM
 * @see PageHierarchy
 * @see WebsiteManager::init()
 * @see WebsiteManager::run()
 */
class WebsiteManager {
	/**
	 * Static instance field for Singleton code design in PHP
	 *
	 * @var WebsiteManager
	 */
	private static $instance;
	/**
	 * The directory in which SiteBuilder itself lives, relative to the document root.
	 * Defaults to '/SiteBuilder/'
	 *
	 * @var string
	 */
	private $frameworkDirectory;
	/**
	 * The directory in which the content files are defined, relative to the document root.
	 * Defaults to '/Content/'
	 *
	 * @var string
	 */
	private $contentDirectory;
	/**
	 * The page hierarchy that this class manages
	 *
	 * @var PageHierarchy
	 */
	private $hierarchy;
	/**
	 * An associative array defining the path of the page to display on any given HTTP error code
	 *
	 * @var array
	 */
	private $errorPagePaths;
	/**
	 * Wether to set a SiteBuilder custom exception handler to automatically show an error page to
	 * the user on a server error.
	 * Defaults to true
	 *
	 * @var bool
	 */
	private $showErrorPageOnException;
	/**
	 * The current page path, as defined by the 'p' GET parameter.
	 * If no 'p' parameter is set, the WebsiteManager will redirect the user to the default page.
	 *
	 * @var string
	 */
	private $currentPagePath;
	/**
	 * The default page path.
	 * Defaults to 'home'
	 *
	 * @var string
	 */
	private $defaultPagePath;

	/**
	 * Returns an instance of WebsiteManager
	 *
	 * @param array $config The configuration parameters to use.
	 * @return WebsiteManager The initialized instance
	 */
	public static function init(array $config = []): WebsiteManager {
		// Check if static instance field is set
		// If yes, throw error: Singleton class already initialized!
		if(isset(WebsiteManager::$instance)) {
			throw new ErrorException("An instance of WebsiteManager has already been initialized!");
		}

		WebsiteManager::$instance = new self($config);
		return WebsiteManager::$instance;
	}

	/**
	 * Normalizes a directory path string, parsin '.', '..' and '\\' strings and adding slashes to
	 * the beginning and end
	 *
	 * @param string $directory The path to process
	 * @return string The normalized directory path
	 */
	public static function normalizeDirectoryString(string $directory): string {
		$directory = PageHierarchy::normalizePathString($directory);
		return "/$directory/";
	}

	/**
	 * Constructor for the WebsiteManager.
	 * To get an instance of this class, use WebsiteManager::init().
	 * The constructor also sets the superglobal '__SiteBuilder_WebsiteManager' to easily get this
	 * instance.
	 *
	 * @see WebsiteManager::init()
	 */
	private function __construct(array $config) {
		$GLOBALS['__SiteBuilder_WebsiteManager'] = &$this;

		if(!isset($config['frameworkDirectory'])) $config['frameworkDirectory'] = '/SiteBuilder/';
		if(!isset($config['contentDirectory'])) $config['contentDirectory'] = '/Content/';

		if(!isset($config['hierarchy'])) {
			// Check if file 'hierarchy.json' in the root of the content directory exists
			// If no, throw error: A PageHierarchy must be defined
			if(file_exists(($path = trim($config['contentDirectory'], '/') . '/hierarchy.json'))) {
				$config['hierarchy'] = PageHierarchy::loadFromJSON($path);
			} else {
				throw new ErrorException("The SiteBuilder default page hierarchy file was not found, and no other file was specified!");
			}
		}

		if(!isset($config['showErrorPageOnException'])) $config['showErrorPageOnException'] = true;
		if(!isset($config['defaultPagePath'])) $config['defaultPagePath'] = 'home';

		$this->setFrameworkDirectory($config['frameworkDirectory']);
		$this->setContentDirectory($config['contentDirectory']);
		$this->setHierarchy($config['hierarchy']);
		$this->setDefaultPagePath($config['defaultPagePath']);
		$this->clearErrorPagePaths();
		$this->setShowErrorPageOnException($config['showErrorPageOnException']);

		if(isset($_GET['p']) && !empty($_GET['p'])) {
			// Get current page path from 'p' GET parameter
			$this->setCurrentPagePath($_GET['p']);
		} else {
			// Redirect to set 'p' GET parameter
			$this->redirectToPage($this->defaultPagePath, true);
		}
	}

	/**
	 * Runs the manager so that the processes that the WebsiteManager handles are executed.
	 * Please note that this method must be called in order for the WebsiteManager to work.
	 */
	public function run(): void {
		// Check if page exists in hierarchy
		// If not, show error 404: Page not found
		if(!$this->hierarchy->isPageDefined($this->currentPagePath)) {
			$this->showErrorPage(404, 400);
		}

		// Include content files for the page, the global header and footer,
		// and the page header and footer
		// Global header and footer paths are relative to page content directory
		// Page header and footer paths are relative to current page path
		$requirePaths = array();

		if($this->hierarchy->isGlobalAttributeDefined('global-header')) array_push($requirePaths, $this->hierarchy->getGlobalAttribute('global-header'));
		if($this->hierarchy->isPageAttributeDefined($this->currentPagePath, 'header')) {
			array_push($requirePaths, dirname($this->currentPagePath) . '/' . $this->hierarchy->getPageAttribute($this->currentPagePath, 'header'));
		}

		array_push($requirePaths, $this->currentPagePath);

		if($this->hierarchy->isPageAttributeDefined($this->currentPagePath, 'footer')) {
			array_push($requirePaths, dirname($this->currentPagePath) . '/' . $this->hierarchy->getPageAttribute($this->currentPagePath, 'footer'));
		}
		if($this->hierarchy->isGlobalAttributeDefined('global-footer')) array_push($requirePaths, $this->hierarchy->getGlobalAttribute('global-footer'));


		foreach($requirePaths as $path) {
			// Check if content file exists
			// If yes, include it
			// If no, show error 501: Page not implemented
			if($this->isContentFileDefined($path)) {
				require $this->getContentFilePath($path);
			} else {
				trigger_error("The path '" . $path . "' does not have a corresponding content file!", E_USER_WARNING);
				$this->showErrorPage(501, 500);
			}
		}

		// Restore default exception handler
		$this->setShowErrorPageOnException(false);
	}

	/**
	 * Redirect the user to a given page path, optionally also keeping other GET parameters.
	 * The redirection works using a HTTP 303 redirect header sent to the browser.
	 * Please note that this will also halt the script after execution.
	 *
	 * @param string $pagePath The page path to redirect to
	 * @param bool $keepGETParams Wether to keep the GET parameters
	 */
	public function redirectToPage(string $pagePath, bool $keepGETParams = false): void {
		$pagePath = PageHierarchy::normalizePathString($pagePath);

		if($keepGETParams) {
			// Get HTTP query without 'p' parameter
			$params = http_build_query(array_diff_key($_GET, array(
					'p' => ''
			)));

			if(!empty($params)) $params = '&' . $params;
		} else {
			$params = '';
		}

		$uri = '?p=' . $pagePath . $params;

		// If redirecting to the same URI, show 508 page to avoid infinite redirecting
		$requestURIWithoutGETParameters = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
		if($requestURIWithoutGETParameters . $uri === $_SERVER['REQUEST_URI']) {
			trigger_error('Infinite loop detected while redirecting! Showing the default error 508 page to avoid infinite redirecting.', E_USER_WARNING);
			$this->showDefaultErrorPage(508);
		}

		// Redirect and die
		header('Location:' . $uri, true, 303);
		die();
	}

	/**
	 * Check if a given content file exists
	 *
	 * @param string $pagePath The path in the content directory to search for
	 * @return bool The boolean result
	 */
	public function isContentFileDefined(string $pagePath): bool {
		try {
			$this->getContentFilePath($pagePath);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	/**
	 * Get the path for the content file of a given page path
	 *
	 * @param string $pagePath The path in the content directory to search for
	 * @return string The computed path
	 */
	public function getContentFilePath(string $pagePath): string {
		$pagePath = PageHierarchy::normalizePathString($pagePath);
		$contentFilePath = $_SERVER['DOCUMENT_ROOT'] . $this->contentDirectory . $pagePath . '.php';

		// Check if content file exists
		// If no, throw error: Content file not found
		if(!file_exists($contentFilePath)) {
			throw new ErrorException("The given path '$pagePath' does not have a corresponding content file!");
		}

		return $contentFilePath;
	}

	/**
	 * Check if the error page path for a given HTTP error code is defined
	 *
	 * @param int $errorCode The HTTP error code to check for
	 * @return bool The boolean result
	 */
	public function isErrorPagePathDefined(int $errorCode): bool {
		try {
			$this->getErrorPagePath($errorCode);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	/**
	 * Get the error page path for a given HTTP error code.
	 * If no custom page path is defined, the manager will also check to see if the SiteBuilder
	 * default error code page path can be used instead.
	 *
	 * @param int $errorCode The HTTP error code to search for
	 * @return string The defined page path
	 */
	public function getErrorPagePath(int $errorCode): string {
		// Check if error page path is defined for the given error code
		// If no, check if the sitebuilder default path for error pages is defined in the hierarchy
		// If also no, throw error: No error page path defined
		if(!isset($this->errorPagePaths[$errorCode])) {
			if($this->hierarchy->isPageDefined(($path = 'error/' . $errorCode))) {
				$this->setErrorPagePath($errorCode, $path);
			} else {
				throw new ErrorException("The page path for the error code '$errorCode' is not defined!");
			}
		}

		return $this->errorPagePaths[$errorCode];
	}

	/**
	 * Getter for the error page paths
	 *
	 * @return array An associative array with the HTTP error codes and the error page paths
	 * @see WebsiteManager::$errorPagePaths
	 */
	public function getAllErrorPagePaths(): array {
		return $this->errorPagePaths;
	}

	/**
	 * Set the error page path for a given HTTP error code.
	 * The error page path must be defined in the page hierarchy and must have a corresponding
	 * content file.
	 *
	 * @param int $errorCode The HTTP error code to define the error page path for
	 * @param string $pagePath The error page path to use
	 * @return self Returns itself for chaining other functions
	 * @see WebsiteManager::$errorPagePaths
	 */
	public function setErrorPagePath(int $errorCode, string $pagePath): self {
		$pagePath = PageHierarchy::normalizePathString($pagePath);

		// Check if error page is in hierarchy
		// If no, throw error: Cannot use undefined error page
		if(!$this->hierarchy->isPageDefined($pagePath)) {
			throw new ErrorException("The given error page path '$pagePath' is not in the page hierarchy!");
		}

		// Check if error page has a content file
		// If no, throw error: Cannot use error page without its content file
		if(!$this->isContentFileDefined($pagePath)) {
			throw new ErrorException("The given error page path '$pagePath' does not have a corresponding content file!");
		}

		$this->errorPagePaths[$errorCode] = $pagePath;
		return $this;
	}

	/**
	 * Undefine the error page path for a given HTTP error code
	 *
	 * @param int $errorCode The error code to undefine the error page path for
	 * @return self Returns itself for chaining other functions
	 */
	public function removeErrorPagePath(int $errorCode): self {
		if(isset($this->errorPagePaths[$errorCode])) {
			unset($this->errorPagePaths[$errorCode]);
		} else {
			trigger_error("No error page path with the given error code '$errorCode' to remove is defined!", E_USER_NOTICE);
		}

		return $this;
	}

	/**
	 * Undefine all error page paths that were previously set
	 *
	 * @return self Returns itself for chaining other functions
	 */
	public function clearErrorPagePaths(): self {
		$this->errorPagePaths = array();
		return $this;
	}

	/**
	 * Shows a custom error page if one is defined, or the SiteBuilder default error page if it
	 * isn't.
	 * If multiple error codes are given, each error code will be checked in sequential order before
	 * resorting to the default.
	 *
	 * @param int ...$errorCodes The error codes to search for
	 */
	public function showErrorPage(int ...$errorCodes): void {
		// Check each error code in order to see if its error page is defined
		// If yes, redirect to it
		foreach($errorCodes as $errorCode) {
			if($this->isErrorPagePathDefined($errorCode)) {
				$this->redirectToPage($this->getErrorPagePath($errorCode));
			}
		}

		// If here, show default error page
		$this->showDefaultErrorPage($errorCodes[0]);
	}

	/**
	 * Outputs a default error page to the browser according to the given HTTP error code.
	 * Please note that this will also halt the script after execution.
	 *
	 * @param int $errorCode The HTTP error code to output
	 */
	public function showDefaultErrorPage(int $errorCode): void {
		http_response_code($errorCode);
		$errorPage = DefaultErrorPage::init($errorCode);
		echo $errorPage->getHTML();
		die();
	}

	/**
	 * Getter for the framework directory
	 *
	 * @return string
	 * @see WebsiteManager::$frameworkDirectory
	 */
	public function getFrameworkDirectory(): string {
		return $this->frameworkDirectory;
	}

	/**
	 * Setter for the framework directory
	 *
	 * @param string $frameworkDirectory
	 * @see WebsiteManager::$frameworkDirectory
	 */
	private function setFrameworkDirectory(string $frameworkDirectory): void {
		$this->frameworkDirectory = WebsiteManager::normalizeDirectoryString($frameworkDirectory);
	}

	/**
	 * Getter for the content directory
	 *
	 * @return string
	 * @see WebsiteManager::$contentDirectory
	 */
	public function getContentDirectory(): string {
		return $this->contentDirectory;
	}

	/**
	 * Setter for the content directory
	 *
	 * @param string $contentDirectory
	 * @see WebsiteManager::$contentDirectory
	 */
	private function setContentDirectory(string $contentDirectory): void {
		$this->contentDirectory = WebsiteManager::normalizeDirectoryString($contentDirectory);
	}

	/**
	 * Getter for the page hierarchy
	 *
	 * @return PageHierarchy
	 * @see WebsiteManager::$hierarchy
	 */
	public function getHierarchy(): PageHierarchy {
		return $this->hierarchy;
	}

	/**
	 * Setter for the page hierarchy
	 *
	 * @param PageHierarchy $hierarchy
	 * @see WebsiteManager::$hierarchy
	 */
	private function setHierarchy(PageHierarchy $hierarchy): void {
		$this->hierarchy = $hierarchy;
	}

	/**
	 * Getter for wether the WebsiteManager shows an error page on an uncaught exception
	 *
	 * @return bool
	 * @see WebsiteManager::$showErrorPageOnException
	 */
	public function isShowErrorPageOnException(): bool {
		return $this->showErrorPageOnException;
	}

	/**
	 * Setter for wether the WebsiteManager should show an error page on an uncaught exception
	 *
	 * @param bool $showErrorPageOnException
	 * @see WebsiteManager::$showErrorPageOnException
	 */
	private function setShowErrorPageOnException(bool $showErrorPageOnException): void {
		$this->showErrorPageOnException = $showErrorPageOnException;

		if($this->showErrorPageOnException) {
			// Set custom exception handler
			set_exception_handler(function (Throwable $e) {
				// Log exception
				error_log('Uncaught ' . $e->__toString(), 4);

				// Show error page
				if($this->isErrorPagePathDefined(500)) {
					$this->redirectToPage($this->getErrorPagePath(500));
				} else {
					$this->showDefaultErrorPage(500);
				}
			});
		} else {
			// Restore previous exception handler
			restore_exception_handler();
		}
	}

	/**
	 * Getter for the current page path
	 *
	 * @return string
	 * @see WebsiteManager::$currentPagePath
	 */
	public function getCurrentPagePath(): string {
		return $this->currentPagePath;
	}

	/**
	 * Setter for the current page path
	 *
	 * @param string $currentPagePath
	 * @see WebsiteManager::$currentPagePath
	 */
	private function setCurrentPagePath(string $currentPagePath): void {
		if(empty($currentPagePath)) {
			throw new ErrorException("The given page path is empty!");
		}

		$p = PageHierarchy::normalizePathString($currentPagePath);

		if($p !== $currentPagePath) {
			// Redirect to normalize page path in request URI
			$this->redirectToPage($p, true);
		}

		$lastCharInURI = substr($_SERVER['REQUEST_URI'], -1);
		if($lastCharInURI === '&') {
			// Redirect to remove trailing '&' in request URI
			$this->redirectToPage($p, true);
		}

		$this->currentPagePath = $p;
	}

	/**
	 * Getter for the default page path
	 *
	 * @return string
	 * @see WebsiteManager::$defaultPagePath
	 */
	public function getDefaultPagePath(): string {
		return $this->defaultPagePath;
	}

	/**
	 * Setter for the default page path
	 *
	 * @param string $defaultPagePath
	 * @see WebsiteManager::$defaultPagePath
	 */
	private function setDefaultPagePath(string $defaultPagePath): void {
		$this->defaultPagePath = PageHierarchy::normalizePathString($defaultPagePath);
	}

}

