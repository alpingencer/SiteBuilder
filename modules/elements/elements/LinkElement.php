<?php

namespace SiteBuilder\Elements;

use function SiteBuilder\normalizePathString;

class LinkElement extends Element {
	private $linkPath;
	private $isAbsolutePath;
	private $innerHTML;

	public static function newInstance(string $linkPath): self {
		return new self($linkPath);
	}

	public function __construct(string $linkPath) {
		parent::__construct();
		$this->setLinkPath($linkPath);
		$this->clearInnerHTML();
	}

	public function getDependencies(): array {
		return array();
	}

	public function getContent(): string {
		// Set inner HTML
		$sb = $GLOBALS['__SiteBuilder_Core'];
		if(empty($this->innerHTML) && $this->linkPath !== '#' && isset($sb->getPageInfoInHierarchy($this->linkPath)['title'])) {
			$innerHTML = $sb->getPageInfoInHierarchy($this->linkPath)['title'];
		} else {
			$innerHTML = $this->innerHTML;
		}

		// Set href
		$href = $this->linkPath;
		if($this->linkPath !== '#') $href = '?p=' . $href;

		// Set id
		if(empty($this->getHTMLID())) {
			$id = '';
		} else {
			$id = ' id="' . $this->getHTMLID() . '"';
		}

		// Set classes
		if(empty($this->getHTMLClasses())) {
			$classes = '';
		} else {
			$classes = ' class="' . $this->getHTMLClasses() . '"';
		}

		return '<a' . $id . $classes . ' href="' . $href . '">' . $innerHTML . '</a>';
	}

	public function setLinkPath(string $linkPath): self {
		$this->setAbsolutePath(substr($linkPath, 0, 1) === '/');
		$linkPath = normalizePathString($linkPath);

		$sb = $GLOBALS['__SiteBuilder_Core'];
		$currentPagePath = $sb->getCurrentPage()->getPagePath();

		if($this->isAbsolutePath || dirname($currentPagePath) === '.') {
			// Absolute path given or current page is top-level
			if($sb->isPageInHierarchy($linkPath)) {
				// Page found
				$this->linkPath = $linkPath;
			} else {
				// Page not found
				$this->linkPath = '#';
				trigger_error("The given link path '/$linkPath' was not found in the page hierarchy!", E_USER_WARNING);
			}
		} else {
			// Current page is not top-level
			// Search one directory higher until page is found
			do {
				$dirname = dirname($currentPagePath);

				if($dirname === '.') {
					$dirname = '';
				} else {
					$dirname .= '/';
				}

				// Search one directory higher
				$searchHierarchyPath = $dirname . $linkPath;

				if($sb->isPageInHierarchy($searchHierarchyPath)) {
					// Link path found
					$this->linkPath = $searchHierarchyPath;
				} else if($dirname === '') {
					// Link path not found
					$this->linkPath = '#';
					trigger_error("The given link path '$linkPath' was not found in the page hierarchy!", E_USER_WARNING);
				}

				$currentPagePath = $dirname;
			} while(!isset($this->linkPath));
		}

		return $this;
	}

	public function clearLinkPath(): self {
		$this->setLinkPath('');
		return $this;
	}

	public function getLinkPath(): string {
		return $this->linkPath;
	}

	private function setAbsolutePath(bool $isAbsolutePath): self {
		$this->isAbsolutePath = $isAbsolutePath;
		return $this;
	}

	public function isAbsolutePath(): bool {
		return $this->isAbsolutePath;
	}

	public function setInnerHTML(string $innerHTML): self {
		$this->innerHTML = $innerHTML;
		return $this;
	}

	public function clearInnerHTML(): self {
		$this->setInnerHTML('');
		return $this;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
