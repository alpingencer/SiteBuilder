<?php

namespace SiteBuilder\PageElement;

class PageLinkElement extends PageElement {
	private $linkPath;
	private $hrefPrefix;
	private $innerHTML;

	public function __construct(string $linkPath) {
		parent::__construct(array());
		$this->linkPath = $linkPath;
		$this->hrefPrefix = '';
		$this->innerHTML = '';
	}

	public static function newInstance(string $linkPath): self {
		return new self($linkPath);
	}

	public function setHrefSuffix(string $hrefSuffix): self {
		$this->hrefPrefix = $hrefSuffix;
		return $this;
	}

	public function getHrefSuffix(): string {
		return $this->hrefPrefix;
	}

	public function setInnerHTML(string $innerHTML): self {
		$this->innerHTML = $innerHTML;
		return $this;
	}

	public function getInnerHTML(): string {
		return $this->innerHTML;
	}

	public function getContent(): string {
		$sb = $GLOBALS['SiteBuilder_Core'];
		$currentPagePath = $sb->page->getPath();

		// Normalize href
		if(substr($this->linkPath, 0, 1) === '/') {
			$href = $this->linkPath;
		} else {
			$dirname = dirname($currentPagePath);
			if($dirname === '.') {
				$href = $this->linkPath;
			} else {
				$href = $dirname . "/" . $this->linkPath;
			}
		}

		if(empty($this->innerHTML) && isset($sb->getPageInHierarchy($href)['title'])) {
			$innerHTML = $sb->getPageInHierarchy($href)['title'];
		} else {
			$innerHTML = $this->innerHTML;
		}

		$href = $this->hrefPrefix . $href;

		return '<a href="' . $href . '">' . $innerHTML . '</a>';
	}

}
