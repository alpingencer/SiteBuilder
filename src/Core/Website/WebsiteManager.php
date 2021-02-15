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
use Eufony\Utils\Classes\Normalizer;
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
	private string $subsite;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		// Redirect to remove multiple forward slashes in URI
		if(str_contains($_SERVER['REQUEST_URI'], '//')) {
			$this->redirectToURI(preg_replace('/\/{2,}/', '/', $_SERVER['REQUEST_URI']));
		}

		// Redirect to remove trailing '/'
		if($_SERVER['REQUEST_URI'] !== '/' && str_ends_with($_SERVER['REQUEST_URI'], '/')) {
			$this->redirectToURI(rtrim($_SERVER['REQUEST_URI'], '/'));
		}

		$this->hierarchy = new PageHierarchy();

		// Fetch the current subsite name from the hierarchy data
		$current_entry_point = explode('/', $_SERVER['REQUEST_URI'], 3)[1];

		foreach(array_keys($this->hierarchy->data()) as $subsite_name) {
			if(in_array($subsite_name, array('shared', 'default'))) {
				continue;
			}

			if($current_entry_point !== $subsite_name) {
				continue;
			}

			$this->subsite = $subsite_name;
		}

		$this->subsite ??= 'default';

		// Fetch the current page from the request URI
		if($this->subsite === 'default') {
			$current_page = trim($_SERVER['REQUEST_URI'], '/');
		} else {
			$current_page = explode('/', $_SERVER['REQUEST_URI'], 3)[2] ?? '';
		}

		$current_page = explode('?', $current_page, 2)[0];

		// Redirect to show current page in URI
		if(empty($current_page)) {
			$current_page = $this->hierarchy->globalAttribute('default-page') ?? 'home';
			$this->redirect($current_page, keep_get_params: true);
		}

		$this->currentPage = $current_page;

		// Check if HTTP method is one of 'GET', 'POST' or 'HEAD'
		// If no, show error 405: Method not allowed
		if(!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST', 'HEAD'))) {
			ExceptionManager::instance()->showErrorPage(405, 400);
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		// Check if page exists in hierarchy
		// If no, show error 404: Page not found
		try {
			$this->hierarchy->page($this->currentPage);
		} catch(PageHierarchyException) {
			ExceptionManager::instance()->showErrorPage(404, 400);
		}

		// Include content files for the subsite bootstrap file, current page and the global header and footer
		// Content files are relative to the subsite directory
		foreach(array('global/bootstrap', 'global/header', $this->currentPage, 'global/footer') as $path) {
			// Check if content file exists
			// If yes, include it
			// If no, throw error: Current page's content file not found
			try {
				$content_file = $this->contentFile($path);
			} catch(IOException) {
				if($path === $this->currentPage) {
					$subsite = $this->subsite();
					throw new IOException("Undefined content file for the path '$path' in subsite '$subsite'");
				} else {
					continue;
				}
			}

			require $content_file;
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

	public function redirect(string $page, string $subsite = null, string|array $get_params = '', bool $keep_get_params = false): void {
		if(is_string($get_params)) {
			$parsed_params = array();
			parse_str($get_params, $parsed_params);
			$get_params = $parsed_params;
		}

		// Build HTTP GET parameters
		$get_params = http_build_query($keep_get_params ? array_merge($_GET, $get_params) : $get_params);

		// Build URI
		$subsite ??= $this->subsite;
		$subsite = $subsite === 'default' ? '' : $subsite . '/';
		$uri = '/' . $subsite . $page . (empty($get_params) ? '' : '?') . $get_params;

		// Redirect and die
		$this->redirectToURI($uri);
	}

	public function refresh(): void {
		header("Refresh:0");
		die();
	}

	public function contentFile(string $page): string {
		$page = Normalizer::filePath($page);

		foreach(array($this->subsite, 'shared') as $subsite) {
			$file_path = "/routes/$subsite/$page.php";

			// Check if content file exists
			// If no, continue: Check next subsite
			if(!File::exists($file_path)) {
				continue;
			}

			$file_path = File::fullPath($file_path);
			return $file_path;
		}

		// If here, throw error: Content file not found
		throw new IOException("Undefined content file for the given path '$page' in the current subsite");
	}

	public function hierarchy(): PageHierarchy {
		return $this->hierarchy;
	}

	public function currentPage(): string {
		return $this->currentPage;
	}

	public function subsite(): string {
		return $this->subsite;
	}

}
