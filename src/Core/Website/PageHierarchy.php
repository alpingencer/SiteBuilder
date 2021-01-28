<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Website;

use ErrorException;
use SiteBuilder\Utils\Classes\JsonDecoder;
use SiteBuilder\Utils\Classes\Normalizer;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;

final class PageHierarchy {
	use ManagedObject;
	use Singleton;

	private array $data;

	public function __construct() {
		$this->setAndAssertManager(WebsiteManager::class);
		$this->assertSingleton();

		$this->data = JsonDecoder::read('/src/content/hierarchy.json');
		$this->prepare();
	}

	private function prepare(): void {
		// Assert valid
		$this->assertValid();

		// Cascade
		foreach($this->data as &$subsite) {
			$this->cascade($subsite);
		}

		// Check if default page is a valid page
		foreach(array_keys($this->data) as $subsite_name) {
			if($subsite_name === 'shared') {
				continue;
			}

			$default_page = $this->globalAttribute('default-page', subsite_name: $subsite_name);
			try {
				$this->page($default_page, subsite_name: $subsite_name);
			} catch(ErrorException) {
				throw new ErrorException("The given default page '$default_page' is not a valid page in the subsite '$subsite_name'!");
			}
		}
	}

	private function assertValid(): void {
		// Check if keys in hierarchy contain '/'
		// If yes, throw error: Invalid subsite or page names
		JsonDecoder::assertTraversable($this->data, '/');

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
				foreach($required_global_attributes as $attribute_name) {
					if(isset($subsite['global'][$attribute_name])) {
						throw new ErrorException("The 'shared' subsite cannot define the '$attribute_name' global attribute!");
					}
				}
			} else {
				foreach($required_global_attributes as $attribute_name) {
					if(!isset($subsite['global'][$attribute_name])) {
						throw new ErrorException("The subsite '$subsite_name' must define the '$attribute_name' global attribute!");
					}
				}
			}

			if(isset($subsite['children'])) {
				// Check if 'children' attribute is an array
				// If no, throw error: 'children' must be an array
				if(!is_array($subsite['children'])) {
					throw new ErrorException("The 'children' attribute of the subsite '$subsite_name' must be an array!");
				}

				// Validate subpages
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

		if(isset($subpage['children'])) {
			// Check if 'children' attribute is an array
			// If no, throw error: 'children' must be an array
			if(!is_array($subpage['children'])) {
				throw new ErrorException("The 'children' attribute of the subsite '$page_name' must be an array!");
			}

			// Validate children
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
			foreach($data as $attribute_name => $attribute) {
				// Skip 'children' and 'global' attributes
				if($attribute_name !== 'children' && $attribute_name !== 'global') {
					$child[$attribute_name] ??= $attribute;
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

		$page = JsonDecoder::traverse($subsite, $path, '/', group: 'children');

		if($page === null && $subsite_name !== 'shared') {
			// Look for page in 'shared' subsite
			$page = $this->page($path, subsite_name: 'shared');
		}

		if($page !== null) {
			// Page found, return it
			return $page;
		} else {
			// Page not found
			throw new ErrorException("The given page path '$path' was not found in the hierarchy!");
		}
	}

	public function attribute(string $attribute_name, string $page, string $subsite_name = null, string $expected_type = null): mixed {
		$page_data = $this->page($page, subsite_name: $subsite_name);

		if(isset($page_data[$attribute_name])) {
			$attribute = $page_data[$attribute_name];

			try {
				Normalizer::assertExpectedType($attribute, $expected_type);
			} catch(ErrorException) {
				$attribute_type = gettype($attribute);
				throw new ErrorException("Expected type '$expected_type' for the attribute '$attribute_name' for the page '$page', received '$attribute_type'!");
			}

			return $attribute;
		} else {
			return null;
		}
	}

	public function currentAttribute(string $attribute_name, string $expected_type = null): mixed {
		return $this->attribute($attribute_name, WebsiteManager::instance()->currentPage(), expected_type: $expected_type);
	}

	public function globalAttribute(string $attribute_name, string $subsite_name = null, string $expected_type = null): mixed {
		$subsite = $this->subsite(subsite_name: $subsite_name);

		if(isset($subsite['global'][$attribute_name])) {
			$attribute = $subsite['global'][$attribute_name];

			try {
				Normalizer::assertExpectedType($attribute, $expected_type);
			} catch(ErrorException) {
				$attribute_type = gettype($attribute);
				throw new ErrorException("Expected type '$expected_type' for the global attribute '$attribute_name', received '$attribute_type'!");
			}

			return $attribute;
		} else if($subsite_name !== 'shared') {
			return $this->globalAttribute($attribute_name, subsite_name: 'shared', expected_type: $expected_type);
		} else {
			return null;
		}
	}

	public function data(): array {
		return $this->data;
	}
}
