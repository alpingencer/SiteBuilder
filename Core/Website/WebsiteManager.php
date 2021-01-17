<?php

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

class WebsiteManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageHierarchy $hierarchy;
	private string $currentPage;
	private string $defaultPage;
	private array $errorPages;
	private string $subsite;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->hierarchy = new PageHierarchy();

		// Fetch the current subsite name from the hierarchy data
		$current_entry_point = ltrim($_SERVER['SCRIPT_NAME'], '/');

		foreach($this->hierarchy->data() as $subsite_name => $subsite) {
			if($subsite['entry-point'] === $current_entry_point) {
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
//			$this->redirect($this->defaultPage, keepGETParams: true);
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);
	}

	public function redirect(string $page, string|array $GETParams = '', bool $keepGETParams = false): void {

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
		return $this->defaultPage;
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
