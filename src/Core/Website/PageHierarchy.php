<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Website;

use Eufony\Utils\Classes\JsonDecoder;
use Eufony\Utils\Classes\Normalizer;
use Eufony\Utils\Exceptions\PageHierarchyException;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Singleton;

final class PageHierarchy {
	use ManagedObject;
	use Singleton;

	private array $data;

	public function __construct() {
		$this->setAndAssertManager(WebsiteManager::class);
		$this->assertSingleton();

		$this->data = JsonDecoder::read('/content/hierarchy.json');
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
			} catch(PageHierarchyException) {
				throw new PageHierarchyException("Invalid page hierarchy: The given default page '$default_page' is not a valid page in the subsite '$subsite_name'!");
			}
		}
	}

	private function assertValid(): void {
		foreach($this->data as $subsite_name => $subsite) {
			// Assert that the subsite name is not '.' or '..': These are invalid subsite names
			assert(
				$subsite_name !== '.' && $subsite_name !== '..',
				new PageHierarchyException("Invalid page hierarchy: '.' and '..' are not valid subsite names")
			);

			// Assert that the subsite is an array
			assert(
				is_array($subsite),
				new PageHierarchyException("Invalid page hierarchy: The subsite '$subsite_name' must be an array")
			);

			// Check if all required global attributes are set
			// If yes in 'shared' subsite, throw error: 'shared' cannot define these global attributes
			// If no otherwise, throw error: Subsites must define these global attributes
			$required_global_attributes = array('title', 'entry-point', 'default-page');

			if($subsite_name === 'shared') {
				foreach($required_global_attributes as $attribute_name) {
					assert(
						!isset($subsite['global'][$attribute_name]),
						new PageHierarchyException("Invalid page hierarchy: The 'shared' subsite cannot define the global attribute '$attribute_name'")
					);
				}
			} else {
				foreach($required_global_attributes as $attribute_name) {
					assert(
						isset($subsite['global'][$attribute_name]),
						new PageHierarchyException("Invalid page hierarchy: The subsite '$subsite_name' must define the global attribute '$attribute_name'")
					);
				}
			}

			if(isset($subsite['children'])) {
				// Assert that the 'children' attribute is an array
				assert(
					is_array($subsite['children']),
					new PageHierarchyException("Invalid page hierarchy: The 'children' attribute of the subsite '$subsite_name' must be an array")
				);

				// Validate subpages
				foreach($subsite['children'] as $subpage_name => $subpage) {
					$this->assertSubpageValid($subpage, $subpage_name, "$subsite_name/$subpage_name");
				}
			}
		}
	}

	private function assertSubpageValid(mixed $subpage, string $page_name, string $current_path): void {
		// Assert that the page name is not '.' or '..': These are invalid subpage names
		assert(
			$page_name !== '.' && $page_name !== '..',
			new PageHierarchyException("Invalid page hierarchy: Invalid subpage name '.' or '..' in the path '$current_path'")
		);

		// Assert that the page name is not 'global': Invalid subpage names
		assert(
			$page_name !== 'global',
			new PageHierarchyException("Invalid page hierarchy: Invalid page name 'global' in the path '$current_path'")
		);

		// Assert that the subpage is an array
		assert(
			is_array($subpage),
			new PageHierarchyException("Invalid page hierarchy: The subpage '$current_path' must be an array")
		);

		// Assert that the invalid attribute 'global' is not set: Subpages cannot defined global attributes
		assert(
			!isset($subpage['global']),
			new PageHierarchyException("Invalid page hierarchy: Invalid attribute 'global' defined for the page '$current_path'")
		);

		// Assert that the required 'title' attribute is set: Pages must have a title
		assert(
			isset($subpage['title']),
			new PageHierarchyException("Invalid page hierarchy: The required attribute 'title' must be defined for the page '$current_path'")
		);

		if(isset($subpage['children'])) {
			// Assert that the 'children' attribute is an array
			assert(
				is_array($subpage['children']),
				new PageHierarchyException("Invalid page hierarchy: The 'children' attribute of the subpage '$current_path' must be an array")
			);

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

		// Assert that the subsite is defined: Cannot return undefined subsite
		assert(
			isset($this->data[$subsite_name]),
			new PageHierarchyException("Failed while getting subsite data: Undefined subsite '$subsite_name'")
		);

		return $this->data[$subsite_name];
	}

	/**
	 * @param string      $path
	 * @param string|null $subsite_name
	 *
	 * @return array
	 * @throws PageHierarchyException
	 */
	public function page(string $path, string $subsite_name = null): array {
		$subsite = $this->subsite(subsite_name: $subsite_name);
		$path = Normalizer::filePath($path);

		$page = JsonDecoder::traverse($subsite['children'], $path, '/', group: 'children');

		if($page === null && $subsite_name !== 'shared') {
			// Look for page in 'shared' subsite
			$page = $this->page($path, subsite_name: 'shared');
		}

		// Assert that the page is found
		assert(
			$page !== null,
			new PageHierarchyException("The given page path '$path' was not found in the hierarchy!")
		);

		return $page;
	}

	public function attribute(string $attribute_name, string $page, string $subsite_name = null): mixed {
		$page_data = $this->page($page, subsite_name: $subsite_name);
		return $page_data[$attribute_name] ?? null;
	}

	public function currentAttribute(string $attribute_name): mixed {
		return $this->attribute($attribute_name, WebsiteManager::instance()->currentPage());
	}

	public function globalAttribute(string $attribute_name, string $subsite_name = null): mixed {
		$subsite = $this->subsite(subsite_name: $subsite_name);

		if(isset($subsite['global'][$attribute_name])) {
			return $subsite['global'][$attribute_name];
		} else if($subsite_name !== 'shared') {
			return $this->globalAttribute($attribute_name, subsite_name: 'shared');
		} else {
			return null;
		}
	}

	public function data(): array {
		return $this->data;
	}

}
