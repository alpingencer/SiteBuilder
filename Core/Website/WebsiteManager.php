<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Bundled\Classes\Normalizer;
use SiteBuilder\Utils\Bundled\Traits\ManagedObject;
use SiteBuilder\Utils\Bundled\Traits\Runnable;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

final class WebsiteManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageHierarchy $hierarchy;
	private string $currentPage;
	private array $errorPages;
	private string $subsite;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->hierarchy = new PageHierarchy();

		// Fetch the current subsite name from the hierarchy data
		$current_entry_point = ltrim($_SERVER['SCRIPT_NAME'], '/');

		foreach($this->hierarchy->data() as $subsite_name => $subsite) {
			if(Normalizer::filePath($subsite['entry-point']) === $current_entry_point) {
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
			$this->currentPage = $_GET['p'];
		} else {
			// Redirect to set 'p' GET parameter
			$this->redirect($this->defaultPage(), keep_get_params: true);
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);
	}

	public function redirect(string $page, string|array $get_params = '', bool $keep_get_params = false): void {
		if(is_string($get_params)) {
			$parsed_params = array();
			parse_str($get_params, $parsed_params);
			$get_params = $parsed_params;
		}

		if($keep_get_params) {
			// Get HTTP query without 'p' parameter
			$kept_params = http_build_query(array_diff_key($_GET, array_merge(array('p' => ''), $get_params)));
			if(!empty($kept_params)) {
				$kept_params = '&' . $kept_params;
			}
		} else {
			$kept_params = '';
		}

		$get_params = implode(' ', array_map(fn(string $param_name, string $param) => "$param_name=\"$param\"", $get_params));

		if(!empty($kept_params)) {
			$get_params .= '&' . $kept_params;
		}

		$uri = '?p=' . $page . $get_params;

		// If redirecting to the same URI, show 508 page to avoid infinite redirecting
//		$requestURIWithoutGETParameters = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
//		if($requestURIWithoutGETParameters . $uri === $_SERVER['REQUEST_URI']) {
//			trigger_error('Infinite loop detected while redirecting! Showing the default error 508 page to avoid infinite redirecting.', E_USER_WARNING);
//			$this->showDefaultErrorPage(508);
//		}

		// Redirect and die
		header('Location:' . $uri, replace: true, response_code: 303);
		die();
	}

	public function refresh(): void {
		header("Refresh:0");
		die();
	}

	public function contentFile(string $page): string {

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

	public function errorPage(int $error_code): string {

	}

	public function errorPages(): array {
		return $this->errorPages;
	}

	public function showErrorPage(int ...$error_codes): void {

	}

	public function showDefaultErrorPage(int $error_code): void {

	}

	public function subsite(): string {
		return $this->subsite;
	}
}
