<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Website;

use ErrorException;
use Eufony\Core\Exception\ExceptionManager;
use Eufony\Core\FrameworkManager;
use Eufony\Utils\Classes\File;
use Eufony\Utils\Classes\Normalizer;
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

	public static function appDir(): string {
		return dirname($_SERVER['DOCUMENT_ROOT']);
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

		// Assert that the current entry point belongs to a subsite: Entry point must be defined in the hierarchy
		assert(
			isset($this->subsite),
			new PageHierarchyException("Invalid page hierarchy: Undefined subsite with the entry point '$current_entry_point'")
		);

		// Redirect to set 'p' GET parameter if empty
		if(!isset($_GET['p']) || empty($_GET['p'])) {
			$this->redirect($this->defaultPage(), keep_get_params: true);
		}

		// Fetch the current page from the 'p' GET parameter
		$p = $_GET['p'];
		$current_page = Normalizer::filePath($p);

		// Redirect to normalize page path in request URI
		if($current_page !== $p) {
			$this->redirect($current_page, keep_get_params: true);
		}

		// Redirect to remove trailing '&' in request URI
		if(str_ends_with($_SERVER['REQUEST_URI'], '&')) {
			$this->redirect($current_page, keep_get_params: true);
		}

		$this->currentPage = $current_page;
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

		// Include content files for the page and the global header and footer
		// Global header and footer paths are relative to the subsite directory
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
				$subsite = $this->subsite();
				throw new ErrorException("Undefined content file for the path '$path' in the subsite '$subsite'");
			}

			require $content_file;
		}
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
			ExceptionManager::instance()->showDefaultErrorPage(508);
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

	public function subsite(): string {
		return $this->subsite;
	}

}
