<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Utils\Bundled\Classes\JsonDecoder;
use SiteBuilder\Utils\Bundled\Traits\ManagedObject;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

final class PageHierarchy {
	use ManagedObject;
	use Singleton;

	private array $data;

	public function __construct() {
		$this->setAndAssertManager(WebsiteManager::class);
		$this->assertSingleton();

		$this->data = JsonDecoder::read('/Content/hierarchy.json');
		$this->prepare();
	}

	private function prepare(): void {
		$this->assertValid();

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

		// Check if default page is a valid page
		foreach(array_keys($this->data) as $subsite_name) {
			$default_page = $this->globalAttribute('default-page', subsite_name: $subsite_name);
			try {
				$this->page($default_page, subsite_name: $subsite_name);
			} catch(ErrorException) {
				throw new ErrorException("The given default page '$default_page' is not a valid page in the subsite '$subsite_name'!");
			}
		}
	}

	private function assertValid(): void {
		foreach($this->data as $subsite_name => $subsite) {
			$required_attributes = array('title', 'entry-point', 'default-page');

			if($subsite_name === '..' || $subsite_name === '.') {
				throw new ErrorException("'..' and '.' are not valid subsite names!");
			}

			if(str_contains($subsite_name, '/')) {
				throw new ErrorException("The subsite '$subsite_name' cannot contain the character '/'!");
			}

			if($subsite_name === 'shared') {
				foreach($required_attributes as $required_attribute) {
					if(isset($subsite[$required_attribute])) {
						throw new ErrorException("The 'shared' subsite cannot define the '$required_attribute' attribute!");
					}
				}
			} else {
				foreach($required_attributes as $required_attribute) {
					if(!isset($subsite[$required_attribute])) {
						throw new ErrorException("The subsite '$subsite_name' must define the '$required_attribute' attribute!");
					}
				}
			}

			$this->assertSubpageValid($subsite, $subsite_name, $subsite_name);
		}
	}

	private function assertSubpageValid(array $subpage, string $page_name, string $current_path): void {
		if($page_name === '..' || $page_name === '.') {
			throw new ErrorException("'..' and '.' in the path '$current_path' are not valid page names!");
		}

		if(str_contains($page_name, '/')) {
			throw new ErrorException("The page '$page_name' cannot contain the character '/' in the path '$current_path'!");
		}

		if(!isset($subpage['title']) && $current_path !== 'shared') {
			throw new ErrorException("The required attribute 'title' is not defined for the path '$current_path' in the hierarchy!");
		}

		if(isset($subpage['children'])) {
			foreach($subpage['children'] as $child_name => $child) {
				$this->assertSubpageValid($child, $child_name, "$current_path/$child_name");
			}
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

	public function subsite(string $subsite_name): array {
		if(!isset($this->data[$subsite_name])) {
			throw new ErrorException("Undefined subsite '$subsite_name'!");
		}

		return $this->data[$subsite_name];
	}

	public function page(string $path, string $subsite_name = null): array {
		if($subsite_name === null) {
			$subsite_name = WebsiteManager::instance()->subsite();
		}

		$subsite = $this->subsite($subsite_name);

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

	public function attribute(string $attribute_name, string $page, string $subsite_name = null): mixed {
		if($subsite_name === null) {
			$subsite_name = WebsiteManager::instance()->subsite();
		}

		$page = $this->page($page, subsite_name: $subsite_name);

		if(!isset($page[$attribute_name])) {
			throw new ErrorException("The given attribute '$attribute_name' is not defined for the given page '$page'!");
		}

		return $page[$attribute_name];
	}

	public function currentAttribute(string $attribute_name): mixed {
		return $this->attribute($attribute_name, WebsiteManager::instance()->currentPage(), subsite_name: null);
	}

	public function globalAttribute(string $attribute_name, string $subsite_name = null): mixed {
		if($subsite_name === null) {
			$subsite_name = WebsiteManager::instance()->subsite();
		}

		$subsite = $this->subsite($subsite_name);

		if(!isset($subsite[$attribute_name])) {
			throw new ErrorException("The given attribute '$attribute_name' is not defined for the current subsite!");
		}

		return $subsite[$attribute_name];
	}

	public function data(): array {
		return $this->data;
	}
}
