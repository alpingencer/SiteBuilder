<?php

namespace SiteBuilder\PageElement;

define('SITEBUILDER_JS_DEPENDENCY', 0);
define('SITEBUILDER_CSS_DEPENDENCY', 1);

class Dependency {
	private $type;
	private $source;
	private $params;

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

	public static function getNormalizedPath(string $sbRootPath, string $source): string {
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sbRootPath . 'modules/page-element/' . $source))) {
			// File in page-element
			return $path;
		} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sbRootPath . $source))) {
			// File in sitebuilder
			return $path;
		} else {
			// File elsewhere
			return $source;
		}
	}

	public static function getHTML(int $type, string $normalizedPath, string $params): string {
		if(!empty($params)) {
			$params .= ' ';
		}

		switch($type) {
			case SITEBUILDER_CSS_DEPENDENCY:
				return '<link rel="stylesheet" type="text/css" ' . $params . 'href="' . $normalizedPath . '">';
				break;
			case SITEBUILDER_JS_DEPENDENCY:
				return '<script ' . $params . 'src="' . $normalizedPath . '"></script>';
				break;
		}
	}

}