<?php

namespace SiteBuilder\PageElement;

class PageLinkElement extends PageElement {
	private $linkHierarchyPath;
	private $hrefSuffix;
	private $innerHTML;

	public static function newInstance(string $linkHierarchyPath): self {
		return new self($linkHierarchyPath);
	}

	public function __construct(string $linkHierarchyPath) {
		parent::__construct(array());
		$this->linkHierarchyPath = $linkHierarchyPath;
		$this->hrefSuffix = '';
		$this->innerHTML = '';
	}

	public function getContent(): string {
		$sb = $GLOBALS['__SiteBuilderCore'];
		$currentPageHierarchyPath = $sb->getCurrentPage()->getHierarchyPath();

		// Normalize path
		if(substr($this->linkHierarchyPath, 0, 1) === '/') {
			$pageHierarchyPath = $this->linkHierarchyPath;
		} else {
			$dirname = dirname($currentPageHierarchyPath);
			if($dirname === '.') {
				$pageHierarchyPath = $this->linkHierarchyPath;
			} else {
				$pageHierarchyPath = $dirname . "/" . $this->linkHierarchyPath;
			}
		}

		if(empty($this->innerHTML) && isset($sb->getPageInHierarchy($pageHierarchyPath)['title'])) {
			$innerHTML = $sb->getPageInHierarchy($pageHierarchyPath)['title'];
		} else {
			$innerHTML = $this->innerHTML;
		}

		$href = '/?p=' . $pageHierarchyPath . $this->hrefSuffix;

		return '<a href="' . $href . '">' . $innerHTML . '</a>';
	}

	public function getLinkHierarchyPath(): string {
		return $this->linkHierarchyPath;
	}

	public function setHrefSuffix(string $hrefSuffix): self {
		$this->hrefSuffix = $hrefSuffix;
		return $this;
	}

	public function getHrefSuffix(): string {
		return $this->hrefSuffix;
	}

	public function setInnerHTML(string $innerHTML): self {
		$this->innerHTML = $innerHTML;
		return $this;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

}
