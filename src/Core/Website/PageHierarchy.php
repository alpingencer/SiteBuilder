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

		$this->data = JsonDecoder::read('/routes/routes.json');
		$this->assertValid($this->data, '/');
	}

	private function assertValid(mixed $group_or_page, string $current_path): void {
		// Assert that the group or page is an array
		if(!is_array($group_or_page)) {
			throw new PageHierarchyException("Invalid page hierarchy: The group or page '$current_path' must be an array");
		}

		if(isset($group_or_page['children'])) {
			$group = $group_or_page;

			// Assert that the 'children' attribute is an array
			if(!is_array($group['children'])) {
				throw new PageHierarchyException("Invalid page hierarchy: The 'children' attribute of the group '$current_path' must be an array");
			}

			// Validate children
			foreach($group['children'] as $child_name => $child) {
				$current_path = ltrim($current_path, '/') . "/$child_name";

				// Assert that the invalid attribute 'global' is not set: Subpages cannot defined global attributes
				if(isset($child['global'])) {
					throw new PageHierarchyException("Invalid page hierarchy: Invalid attribute 'global' defined for the page '$current_path'");
				}

				$this->assertValid($child, $current_path);
			}
		}
	}

	/**
	 * @param string $path
	 *
	 * @return array
	 * @throws PageHierarchyException
	 */
	public function page(string $path): array {
		$path = Normalizer::pagePath($path);
		$page = JsonDecoder::traverse($this->data['children'], $path, '/', group: 'children');

		// Assert that the page is found
		$page ?? throw new PageHierarchyException("The given page path '$path' was not found in the hierarchy!");

		// If page symlinks another page, merge attributes
		if(isset($page['symlink'])) $page = array_diff_key(array_merge($this->page($page['symlink']), $page), array('symlink' => ''));

		// Return the result
		return $page;
	}

	public function realPath(string $path): string {
		$path = Normalizer::pagePath($path);
		$page = JsonDecoder::traverse($this->data['children'], $path, '/', group: 'children');

		// If the page wasn't found in the hierarchy, return the given path
		if($page === null) return $path;

		return isset($page['symlink'])
			// If page symlinks another page, get real path of symlinked page
			? $this->realPath($page['symlink'])
			: $path;
	}

	public function attribute(string $attribute, string $path, bool $bubble = false): mixed {
		$path = Normalizer::pagePath($path);
		$page = $this->page($path);

		return $page[$attribute]
			?? ($bubble
				// Search group attributes
				? $this->groupAttribute($attribute, dirname($path), bubble: true)
				: null);
	}

	public function currentAttribute(string $attribute, bool $bubble = false): mixed {
		return $this->attribute($attribute, WebsiteManager::instance()->currentPage(), bubble: $bubble);
	}

	public function globalAttribute(string $attribute): mixed {
		return $this->data['global'][$attribute] ?? null;
	}

	public function groupAttribute(string $attribute, string $group, bool $bubble = true): mixed {
		if($group === '/') return $this->globalAttribute($attribute);

		$group_data = $this->page($group);

		return $group_data['group'][$attribute]
			?? ($bubble
				// Search one group higher until the root has been reached
				? $this->groupAttribute($attribute, dirname($group), bubble: true)
				: null);
	}

	public function data(): array {
		return $this->data;
	}

}
