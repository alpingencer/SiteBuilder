<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Utils\Bundled\Classes\JsonDecoder;
use SiteBuilder\Utils\Bundled\Classes\Normalizer;
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
			// Check if type of the subsite is an array
			// If no, throw error: Subsite must be an array
			if(!is_array($subsite)) {
				throw new ErrorException("The given subsite '$subsite_name' in the hierarchy must be an array!");
			}

			// Check if subsite name is '.' or '..'
			// If yes, throw error: Invalid subsite name
			if($subsite_name === '..' || $subsite_name === '.') {
				throw new ErrorException("'..' and '.' are not valid subsite names!");
			}

			// Check if subsite name contains '/'
			// If yes, throw error: invalid subsite name
			if(str_contains($subsite_name, '/')) {
				throw new ErrorException("The subsite '$subsite_name' cannot contain the character '/'!");
			}

			// Check if 'global' global attribute is set
			// If yes, throw error: Invalid global attribute name
			if(isset($subsite['global']['global'])) {
				throw new ErrorException("Invalid global attribute 'global' defined for the subsite '$subsite_name' in the hierarchy!");
			}

			// Check if all required global attributes are set
			// If yes in 'shared' subsite, throw error: 'shared' cannot define these global attributes
			// If no otherwise, throw error: Subsites must define these global attributes
			$required_global_attributes = array('title', 'entry-point', 'default-page');

			if($subsite_name === 'shared') {
				foreach($required_global_attributes as $required_attribute) {
					if(isset($subsite['global'][$required_attribute])) {
						throw new ErrorException("The 'shared' subsite cannot define the '$required_attribute' global attribute!");
					}
				}
			} else {
				foreach($required_global_attributes as $required_attribute) {
					if(!isset($subsite['global'][$required_attribute])) {
						throw new ErrorException("The subsite '$subsite_name' must define the '$required_attribute' global attribute!");
					}

					if(!is_string(gettype($subsite['global'][$required_attribute]))) {
						throw new ErrorException();
					}
				}
			}

			// Validate subpages
			if(isset($subsite['children'])) {
				// Check if 'children' attribute is an array
				// If no, throw error: 'children' must be an array
				if(!is_array($subsite['children'])) {
					throw new ErrorException("The 'children' attribute of the subsite '$subsite_name' must be an array!");
				}

				foreach($subsite['children'] as $subpage_name => $subpage) {
					$this->assertSubpageValid($subpage, $subpage_name, "$subsite_name/$subpage_name");
				}
			}
		}
	}

	private function assertSubpageValid(mixed $subpage, string $page_name, string $current_path): void {
		// Check if type of the subpage is an array
		// If no, throw error: Subpage must be an array
		if(!is_array($subpage)) {
			throw new ErrorException("The given subpage '$page_name' in the page '$current_path' in the hierarchy must be an array!");
		}

		// Check if page name is '.' or '..'
		// If yes, throw error: Invalid page name
		if($page_name === '..' || $page_name === '.') {
			throw new ErrorException("'..' and '.' in the path '$current_path' are not valid page names!");
		}

		// Check if page name contains '/'
		// If yes, throw error: Invalid page name
		if(str_contains($page_name, '/')) {
			throw new ErrorException("The page '$page_name' cannot contain the character '/' in the path '$current_path'!");
		}

		// Check if page name is 'global'
		// If yes, throw error: Invalid page name
		if($page_name === 'global') {
			throw new ErrorException("Invalid page name 'global' in the path '$current_path'!");
		}

		// Check if invalid attribute 'global' is set
		// If yes, throw error: Subpages cannot define global attributes
		if(isset($subpage['global'])) {
			throw new ErrorException("Invalid attribute 'global' defined for the page '$current_path' in the hierarchy!");
		}

		// Check if required 'title' attribute is set
		// If no, throw error: Page must have a title
		if(!isset($subpage['title'])) {
			throw new ErrorException("The required attribute 'title' is not defined for the path '$current_path' in the hierarchy!");
		}

		// Check if the given title is a string
		// If no, throw error: Unexpected type
		if(!is_string($subpage['title'])) {
			throw new ErrorException("Expected type 'string' for the attribute 'title' for the page '$current_path' in the hierarchy!");
		}

		// Validate children of page
		if(isset($subpage['children'])) {
			// Check if 'children' attribute is an array
			// If no, throw error: 'children' must be an array
			if(!is_array($subpage['children'])) {
				throw new ErrorException("The 'children' attribute of the subsite '$page_name' must be an array!");
			}

			foreach($subpage['children'] as $child_name => $child) {
				$this->assertSubpageValid($child, $child_name, "$current_path/$child_name");
			}
		}
	}

	private function cascade(array &$data): void {
		// Check if attribute 'children' is set
		// If no, return: Nothing to cascade
		if(!isset($data['children'])) {
			return;
		}

		foreach($data['children'] as &$child) {
			foreach(array_keys($data) as $key) {
				// Skip 'children' and 'global' attributes
				if($key !== 'children' && $key !== 'global') {
					$child[$key] ??= $data[$key];
				}

				$this->cascade($child);
			}
		}
	}

	public function subsite(string $subsite_name = null): array {
		// Default to current subsite
		if($subsite_name === null) {
			$subsite_name = WebsiteManager::instance()->subsite();
		}

		// Check if subsite is defined
		// If no, throw error: Subsite not found
		if(!isset($this->data[$subsite_name])) {
			throw new ErrorException("Undefined subsite '$subsite_name'!");
		}

		return $this->data[$subsite_name];
	}

	public function page(string $path, string $subsite_name = null): array {
		$subsite = $this->subsite(subsite_name: $subsite_name);
		$path = Normalizer::filePath($path);

		// Start with subsite root
		$current_page = $subsite;

		// Split page path into segments
		$segments = explode('/', $path);

		// Traverse page hierarchy and find current page
		foreach($segments as $segment) {
			if(isset($current_page['children'][$segment])) {
				$current_page = $current_page['children'][$segment];
			} else {
				throw new ErrorException("The given page path '$path' was not found in the hierarchy!");
			}
		}

		// Return the found page
		return $current_page;
	}

	public function attribute(string $attribute_name, string $page, string $subsite_name = null, mixed $default = null, string $expected_type = null): mixed {
		$page = $this->page($page, subsite_name: $subsite_name);

		if(isset($page[$attribute_name])) {
			$attribute = $page[$attribute_name];
			$attribute_type = gettype($attribute);

			if($expected_type !== null && $attribute_type !== $expected_type) {
				throw new ErrorException("Expected type '$expected_type' for the attribute '$attribute_name' for the page '$page', received '$attribute_type'!");
			}

			return $attribute;
		} else {
			return $default;
		}
	}

	public function currentAttribute(string $attribute_name, mixed $default = null, string $expected_type = null): mixed {
		return $this->attribute($attribute_name, WebsiteManager::instance()->currentPage(), default: $default, expected_type: $expected_type);
	}

	public function globalAttribute(string $attribute_name, string $subsite_name = null, mixed $default = null, string $expected_type = null): mixed {
		$subsite = $this->subsite(subsite_name: $subsite_name);

		if(isset($subsite['global'][$attribute_name])) {
			$attribute = $subsite['global'][$attribute_name];
			$attribute_type = gettype($attribute);

			if($expected_type !== null && $attribute_type !== $expected_type) {
				throw new ErrorException("Expected type '$expected_type' for the global attribute '$attribute_name', received '$attribute_type'!");
			}

			return $attribute;
		} else {
			return $default;
		}
	}

	public function data(): array {
		return $this->data;
	}
}
