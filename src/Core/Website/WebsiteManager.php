<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Core\Content\ContentManager;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Classes\File;
use SiteBuilder\Utils\Classes\JsonDecoder;
use SiteBuilder\Utils\Classes\Normalizer;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

final class WebsiteManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageHierarchy $hierarchy;
	private string $currentPage;
	private array $errorPages;
	private string $subsite;

	public static function appDir(): string {
		return dirname($_SERVER['DOCUMENT_ROOT']);
	}

	public static function frameworkDir(): string {
		return '/vendor/sitebuilder/sitebuilder';
	}

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->hierarchy = new PageHierarchy();

		// Fetch the current subsite name from the hierarchy data
		$current_entry_point = ltrim($_SERVER['SCRIPT_NAME'], '/');

		foreach(array_keys($this->hierarchy->data()) as $subsite_name) {
			if($subsite_name === 'shared') {
				continue;
			}

			$subsite_entry_point = $this->hierarchy->globalAttribute('entry-point', subsite_name: $subsite_name);
			$subsite_entry_point = Normalizer::filePath($subsite_entry_point);

			if($subsite_entry_point === $current_entry_point) {
				$this->subsite = $subsite_name;
			}
		}

		// Check if the current entry point belongs to a subsite
		// If no, throw error: Subsite not found
		if(!isset($this->subsite)) {
			throw new ErrorException("The current subsite with the entry point '$current_entry_point' is not defined in the page hierarchy!");
		}

		if(isset($_GET['p']) && !empty($_GET['p'])) {
			// Get the current page path from the 'p' GET parameter
			$p = $_GET['p'];

			$current_page = Normalizer::filePath($p);

			if($current_page !== $p) {
				// Redirect to normalize page path in request URI
				$this->redirect($current_page, keep_get_params: true);
			}

			$last_char_in_uri = substr($_SERVER['REQUEST_URI'], -1);

			if($last_char_in_uri === '&') {
				// Redirect to remove trailing '&' in request URI
				$this->redirect($current_page, keep_get_params: true);
			}

			$this->currentPage = $current_page;
		} else {
			// Redirect to set 'p' GET parameter
			$this->redirect($this->defaultPage(), keep_get_params: true);
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		// Check if page exists in hierarchy
		// If not, show error 404: Page not found
		try {
			$this->hierarchy->page($this->currentPage);
		} catch(ErrorException) {
			$this->showErrorPage(404, 400);
		}


		// Include content files for the page and the global header and footer
		// Global header and footer paths are relative to page content directory
		$require_paths = array();

		$global_header = $this->hierarchy->globalAttribute('header');
		if($global_header !== null) {
			array_push($require_paths, $global_header);
		}

		array_push($require_paths, $this->currentPage);

		$global_footer = $this->hierarchy->globalAttribute('footer');
		if($global_footer !== null) {
			array_push($require_paths, $global_footer);
		}

		foreach($require_paths as $path) {
			// Check if content file exists
			// If yes, include it
			// If no, show error 501: Page not implemented
			try {
				$content_file = $this->contentFile($path);
			} catch(ErrorException) {
				trigger_error("The path '" . $path . "' does not have a corresponding content file!", E_USER_WARNING);
				$this->showErrorPage(501, 500);
			}

			require $content_file;
		}

		/*
		// Restore default exception handler
		$this->setShowErrorPageOnException(false);
		*/
	}

	public function redirect(string $page, string|array $get_params = '', bool $keep_get_params = false): void {
		if(is_string($get_params)) {
			$parsed_params = array();
			parse_str($get_params, $parsed_params);
			$get_params = $parsed_params;
		}

		if($keep_get_params) {
			// Get HTTP query without 'p' and given GET parameters
			$kept_params = http_build_query(array_diff_key($_GET, array_merge(array('p' => ''), $get_params)));
			if(!empty($kept_params)) {
				$kept_params = '&' . $kept_params;
			}
		} else {
			$kept_params = '';
		}

		// Build URI
		$get_params = http_build_query($get_params);

		if(!empty($get_params)) {
			$get_params = '&' . $get_params;
		}

		if(!empty($kept_params)) {
			$get_params = $kept_params . $get_params;
		}

		$uri = '?p=' . $page . $get_params;

		// If redirecting to the same URI, show 508 page to avoid infinite redirecting
		$request_uri_without_get_params = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

		if($request_uri_without_get_params . $uri === $_SERVER['REQUEST_URI']) {
			trigger_error('Infinite loop detected while redirecting! Showing the default error 508 page to avoid infinite redirecting.', E_USER_WARNING);
			$this->showDefaultErrorPage(508);
		}

		// Redirect and die
		header('Location:' . $uri, replace: true, response_code: 303);
		die();
	}

	public function refresh(): void {
		header("Refresh:0");
		die();
	}

	public function contentFile(string $page): string {
		$page = Normalizer::filePath($page);

		foreach(array($this->subsite, 'shared') as $subsite) {
			$file_path = "/src/content/$subsite/$page.php";

			// Check if content file exists
			// If no, continue: Check next subsite
			if(!File::exists($file_path)) {
				continue;
			}

			$file_path = File::fullPath($file_path);
			return $file_path;
		}

		// If here, throw error: Content file not found
		throw new ErrorException("Undefined content file for the given path '$page' in the subsite '$subsite'!");
	}

	public function hierarchy(): PageHierarchy {
		return $this->hierarchy;
	}

	public function currentPage(): string {
		return $this->currentPage;
	}

	public function defaultPage(): string {
		return $this->hierarchy->globalAttribute('default-page');
	}

	public function errorPage(int $error_code, string $error_page = null): string {
		if($error_page === null) {
			// Check if error page path is defined for the given error code
			// If no, check if the SiteBuilder default path for error pages is defined in the hierarchy
			// If also no, throw error: No error page path defined
			if(!isset($this->errorPages[$error_code])) {
				$error_page = "error/$error_code";

				try {
					$this->hierarchy->page($error_page);
				} catch(ErrorException) {
					throw new ErrorException("The page path for the error code '$error_code' is undefined!");
				}

				$this->errorPages[$error_code] = $error_page;
			}

			return $this->errorPages[$error_code];
		} else {
			$error_page = Normalizer::filePath($error_page);

			// Check if error page is in hierarchy
			// If no, throw error: Cannot use undefined error page
			try {
				$this->hierarchy->page($error_page);
			} catch(ErrorException) {
				throw new ErrorException("The given error page path '$error_page' is not in the page hierarchy!");
			}

			// Check if error page has a content file
			// If no, throw error: Cannot use error page without its content file
			try {
				$this->contentFile($error_page);
			} catch(ErrorException) {
				throw new ErrorException("The given error page path '$error_page' does not have a corresponding content file!");
			}

			$this->errorPages[$error_code] = $error_page;
			return $this;
		}
	}

	public function errorPages(): array {
		return $this->errorPages;
	}

	public function showErrorPage(int ...$error_codes): void {
		// Check each error code in order to see if its error page is defined
		// If yes, redirect to it
		// If no, show default error page
		foreach($error_codes as $error_code) {
			try {
				$this->redirect($this->errorPage($error_code));
			} catch(ErrorException) {
				if(count($error_codes) > 2) {
					$this->showErrorPage(array_slice($error_codes, 1));
				} else {
					$this->showDefaultErrorPage($error_codes[0]);
				}
			}
		}
	}

	public function showDefaultErrorPage(int $error_code): void {
		http_response_code($error_code);
		$error_pages = JsonDecoder::read('/vendor/sitebuilder/sitebuilder/src/Core/Website/default-error-pages.json');

		if(isset($error_pages[$error_code])) {
			$error_name = $error_pages[$error_code]['name'];
			$error_message = $error_pages[$error_code]['message'];
		} else {
			$error_name = 'Unknown Error';
			$error_message = 'An unknown error has occurred';
		}

		$content_manager = ContentManager::instance();
		$content_manager->clear();

		$content_manager->lang('en');
		$content_manager->head = "<title>$error_code $error_name</title>";
		$content_manager->body = "<h1>$error_code $error_name</h1><p>$error_message</p>";

		$content_manager->output();
		die();
	}

	public function subsite(): string {
		return $this->subsite;
	}

}
