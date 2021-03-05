<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Website;

use Eufony\Core\Exception\ExceptionManager;
use Eufony\Core\FrameworkManager;
use Eufony\Utils\Classes\File;
use Eufony\Utils\Exceptions\IOException;
use Eufony\Utils\Exceptions\PageHierarchyException;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Runnable;
use Eufony\Utils\Traits\Singleton;

final class WebsiteManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageHierarchy $hierarchy;
	private string $currentPage;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		// Redirect to remove multiple and trailing forward slashes in URI
		$normalized_uri = rtrim(preg_replace('/\/{2,}/', '/', $_SERVER['REQUEST_URI']), '/');
		if(!empty($normalized_uri) && $normalized_uri !== $_SERVER['REQUEST_URI']) $this->redirectToURI($normalized_uri);

		// Initialize page hierarchy
		$this->hierarchy = new PageHierarchy();

		// Fetch the current page from the request URI
		$current_page = $_SERVER['REQUEST_URI'];
		if($current_page === '/') $current_page = $this->hierarchy->globalAttribute('default-page');
		$current_page = explode('?', $current_page, 2)[0];
		$this->currentPage = $current_page;

		// If page doesn't exist in hierarchy, show error 404: Page not found
		try {
			$this->hierarchy->page($this->currentPage);
		} catch(PageHierarchyException) {
			ExceptionManager::instance()->showErrorPage(404, 400);
		}

		// If HTTP method is not allowed, show error 405: Method not allowed
		$allow_methods = $this->hierarchy->currentAttribute('methods') ?? array('get', 'head');
		if(is_string($allow_methods)) $allow_methods = explode(',', str_replace(' ', '', $allow_methods));
		if(!in_array(strtolower($_SERVER['REQUEST_METHOD']), $allow_methods)) ExceptionManager::instance()->showErrorPage(405, 400);
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		// Include content files for all header and footer files and the current page
		// Headers
		$current_path = '';
		$segments = dirname($this->currentPage) === '/'
			? array('')
			: explode('/', dirname($this->currentPage));

		foreach($segments as $segment) {
			$current_path .= "/$segment";
			try {
				$content_file = $this->contentFile("$current_path/header");
				require $content_file;
			} catch(IOException) {
				continue;
			}
		}

		// Current page
		require $this->contentFile($this->currentPage);

		// Footers
		$current_path = '';

		foreach($segments as $segment) {
			$current_path .= "/$segment";
			try {
				$content_file = $this->contentFile("$current_path/footer");
				require $content_file;
			} catch(IOException) {
				continue;
			}
		}
	}

	public function redirectToURI(string $uri): void {
		// If redirecting to the same URI, show 508 page to avoid infinite redirecting
		if($uri === $_SERVER['REQUEST_URI']) {
			ExceptionManager::instance()->showDefaultErrorPage(508);
		}

		header('Location:' . $uri, replace: true, response_code: 303);
		die();
	}

	public function redirect(string $page, string|array $get_params = '', bool $keep_get_params = false): void {
		if(is_string($get_params)) {
			$parsed_params = array();
			parse_str($get_params, $parsed_params);
			$get_params = $parsed_params;
		}

		// Build HTTP GET parameters
		$get_params = http_build_query($keep_get_params ? array_merge($_GET, $get_params) : $get_params);

		// Build URI
		$uri = '/' . $page . (empty($get_params) ? '' : '?') . $get_params;

		// Redirect and die
		$this->redirectToURI($uri);
	}

	public function refresh(): void {
		header("Refresh:0");
		die();
	}

	public function contentFile(string $page): string {
		// Follow symlinks and get the real path
		$page = $this->hierarchy->realPath($page);

		$file_path = "/routes/$page.php";

		// Assert that the file exists
		if(!File::exists($file_path)) {
			throw new IOException("Undefined content file for the given path '$page'");
		}

		// Get the full file path
		$file_path = File::fullPath($file_path);

		// Return the result
		return $file_path;
	}

	public function hierarchy(): PageHierarchy {
		return $this->hierarchy;
	}

	public function currentPage(): string {
		return $this->currentPage;
	}

}
