<?php

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Utils\JsonDecoder;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;

class PageHierarchy {
	use ManagedObject;
	use Singleton;

	private array $data;

	public function __construct() {
		$this->setAndAssertManager(WebsiteManager::instanceOrNull());
		$this->assertSingleton();

		$this->data = JsonDecoder::read('/Content/hierarchy.json');
		$this->assertValid();
		$this->prepare();
	}

	private function assertValid(): void {
		foreach($this->data as $subsite_name => $subsite) {
			if($subsite_name === 'shared') {
				if(isset($subsite['title']) || isset($subsite['entry-point'])) {
					throw new ErrorException("The 'shared' subsite cannot define the 'title' or 'entry-point' attributes!");
				}
			} else {
				if(!isset($subsite['title']) || !isset($subsite['entry-point'])) {
					throw new ErrorException("The subsite '$subsite_name' must define the 'title' and 'entry-point' attributes!");
				}
			}

			$this->assertSubpageValid($subsite, $subsite_name);
		}
	}

	private function assertSubpageValid(array $subpage, string $current_path): void {
		if(!isset($subpage['title']) && $current_path !== 'shared') {
			throw new ErrorException("The required attribute 'title' is not defined for the path '$current_path' in the hierarchy!");
		}

		if(isset($subpage['children'])) {
			foreach($subpage['children'] as $child_name => $child) {
				$this->assertSubpageValid($child, "$current_path/$child_name");
			}
		}
	}

	private function prepare(): void {
		// Copy and destroy 'shared'
		if(isset($this->data['shared'])) {
			foreach($this->data as $subsite_name => &$subsite) {
				if($subsite_name === 'shared') {
					continue;
				}

				$subsite = array_merge_recursive_distinct($this->data['shared'], $subsite);
			}

			unset($this->data['shared']);
		}

		// Cascade
		foreach($this->data as &$subsite) {
			$this->cascade($subsite);
		}
	}

	private function cascade(array &$data): void {
		if(!isset($data['children'])) {
			return;
		}

		foreach($data['children'] as &$child) {
			foreach(array_keys($data) as $key) {
				if($key !== 'children') {
					$child[$key] ??= $data[$key];
				}

				$this->cascade($child);
			}
		}
	}

	public function subsite() {
		return $this->data[WebsiteManager::instance()->subsite()];
	}

	public function page(string $path): array {
		$subsite = $this->subsite();

		// Start with subsite root
		$current_page = &$subsite;

		// Split page path into segments
		$segments = explode('/', $path);

		// Traverse page hierarchy and find current page
		foreach($segments as $segment) {
			if(isset($current_page['children']) && isset($current_page['children'][$segment])) {
				$current_page = &$current_page['children'][$segment];
			} else {
				throw new ErrorException("The given page path '$path' was not found in the hierarchy!");
			}
		}

		// Return the found page
		return $current_page;
	}

	public function attribute(string $attribute_name, string $page): mixed {
		$page = $this->page($page);

		if(!isset($page[$attribute_name])) {
			throw new ErrorException("The given attribute '$attribute_name' is not defined for the given page '$page'!");
		}

		return $page[$attribute_name];
	}

	public function currentAttribute(string $attribute_name): mixed {
		return $this->attribute($attribute_name, WebsiteManager::instance()->currentPage());
	}

	public function globalAttribute(string $attribute_name): mixed {
		$subsite = $this->subsite();

		if(!isset($subsite[$attribute_name])) {
			throw new ErrorException("The given attribute '$attribute_name' is not defined for the current subsite!");
		}

		return $subsite[$attribute_name];
	}

	public function data(): array {
		return $this->data;
	}
}
