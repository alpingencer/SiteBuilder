<?php

namespace SiteBuilder\Elements;

define('__SITEBUILDER_JS_DEPENDENCY', 0);
define('__SITEBUILDER_CSS_DEPENDENCY', 1);

class Dependency {
	private $type;
	private $source;
	private $params;

	public static function getNormalizedPath(string $sitebuilderDirectoryPath, string $source): string {
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sitebuilderDirectoryPath . 'modules/elements/external/' . $source))) {
			// File in external-resources
			return $path;
		} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $sitebuilderDirectoryPath . 'modules/elements/' . $source))) {
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

	public static function removeDuplicates(array &$dependencies): void {
		$addedDependencies = array();
		$addedDependencySources = array();

		foreach($dependencies as $dependency) {
			if(in_array($dependency->getSource(), $addedDependencySources, true)) continue;

			array_push($addedDependencySources, $dependency->getSource());
			array_push($addedDependencies, $dependency);
		}

		$dependencies = $addedDependencies;
	}

	public function __construct(int $type, string $source, string $params = '') {
		$this->setType($type);
		$this->setSource($source);
		$this->setParams($params);
	}

	private function setType(int $type): self {
		$this->type = $type;
		return $this;
	}

	public function getType(): int {
		return $this->type;
	}

	private function setSource(string $source): self {
		$this->source = $source;
		return $this;
	}

	public function getSource(): string {
		return $this->source;
	}

	private function setParams(string $params): self {
		$this->params = $params;
		return $this;
	}

	private function clearParams(): self {
		$this->setParams('');
		return $this;
	}

	public function getParams(): string {
		return $this->params;
	}

	public function getHTML(): string {
		$sb = $GLOBALS['__SiteBuilder_Core'];
		$normalizedPath = static::getNormalizedPath($sb->getFrameworkDirectory(), $this->source);

		if(empty($this->params)) {
			$params = '';
		} else {
			$params = $this->params . ' ';
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
