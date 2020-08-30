<?php

namespace SiteBuilder\PageElement;

define('__SITEBUILDER_JS_DEPENDENCY', 0);
define('__SITEBUILDER_CSS_DEPENDENCY', 1);

class Dependency {
	private $type;
	private $source;
	private $params;

	public static function getNormalizedPath(string $sitebuilderDirectoryPath, string $source): string {
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sitebuilderDirectoryPath . 'modules/page-element/' . $source))) {
			// File in page-element
			return $path;
		} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sitebuilderDirectoryPath . $source))) {
			// File in sitebuilder
			return $path;
		} else {
			// File elsewhere
			return $source;
		}
	}

	public function __construct(int $type, string $source, string $params = '') {
		$this->type = $type;
		$this->source = $source;
		$this->params = $params;
	}

	public function getType(): int {
		return $this->type;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function getParams(): string {
		return $this->params;
	}

	public function getHTML(): string {
		$params = $this->params;
		$normalizedPath = self::getNormalizedPath($GLOBALS['__SiteBuilderCore']->getSiteBuilderDirectoryPath(), $this->source);

		if(!empty($params)) {
			$params .= ' ';
		}

		switch($this->type) {
			case __SITEBUILDER_CSS_DEPENDENCY:
				return '<link rel="stylesheet" type="text/css" ' . $params . 'href="' . $normalizedPath . '">';
				break;
			case __SITEBUILDER_JS_DEPENDENCY:
				return '<script ' . $params . 'src="' . $normalizedPath . '"></script>';
				break;
		}
	}

}