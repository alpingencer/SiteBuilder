<?php

namespace SiteBuilder\Core\CM;

define('__SITEBUILDER_JS_DEPENDENCY', 0);
define('__SITEBUILDER_CSS_DEPENDENCY', 1);

/**
 * The Dependency class provides a convenient way for components to find and add CSS and JS
 * dependencies to the page head.
 * To use them, define an array of dependencies in the components
 * getDependencies() method.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM
 * @see Component::getDependencies()
 */
class Dependency {
	/**
	 * The type of the dependency, as defined by the '__SITEBUILDER_JS_DEPENDENCY' and
	 * '__SITEBUILDER_CSS_DEPENDENCY' constants
	 *
	 * @var int
	 */
	private $type;
	/**
	 * The HTML source path of the dependency.
	 * Note that this doesn't have to be an absolute path, as any given relative path will be found
	 * and normalize by the dependency.
	 *
	 * @var string
	 */
	private $source;
	/**
	 * Any additional HTML attributes to add to the generated dependency string
	 *
	 * @var string
	 */
	private $params;

	/**
	 * Returns an instance of Dependency
	 *
	 * @param int $type The type of the dependency
	 * @param string $source The source string of the dependency
	 * @param string $params Optional additional HTML attributes of the dependency
	 * @return Dependency The initialized instance
	 */
	public static function init(int $type, string $source, string $params = ''): Dependency {
		return new self($type, $source, $params);
	}

	/**
	 * Removes dependencies with duplicate sources from a given array
	 *
	 * @param array $dependencies The array to process
	 */
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

	/**
	 * Searches for a given source within the framework directory and returns a normalized path if
	 * the resource is found.
	 *
	 * @param string $frameworkDirectory The directory in which SiteBuilder lives
	 * @param string $source The source path to search for
	 * @return string The normalized path
	 */
	public static function getNormalizedPath(string $frameworkDirectory, string $source): string {
		if(substr($source, 0, 1) === '/') {
			return $source;
		}

		if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $frameworkDirectory . 'Modules/Components/external/' . $source))) {
			// File in external
			return $path;
		} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $frameworkDirectory . 'Modules/Components/' . $source))) {
			// File in Components
			return $path;
		} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . ($path = $frameworkDirectory . $source))) {
			// File in SiteBuilder
			return $path;
		} else {
			// File elsewhere
			return $source;
		}
	}

	/**
	 * Constructor for the dependency.
	 * To get an instance of this class, use Dependency::init()
	 *
	 * @param string $frameworkDirectory The directory in which SiteBuilder lives
	 * @param string $source The source path to search for
	 * @see Dependency::init()
	 */
	private function __construct(int $type, string $source, string $params) {
		$this->setType($type);
		$this->setSource($source);
		$this->setParams($params);
	}

	/**
	 * Builds and returns the HTML string of the dependency based on it's type, source and
	 * parameters.
	 *
	 * @return string The generated HTML string
	 */
	public function getHTML(): string {
		$cm = $GLOBALS['__SiteBuilder_ContentManager'];
		$normalizedPath = Dependency::getNormalizedPath($cm->getFrameworkDirectory(), $this->source);

		if(empty($this->params)) {
			$params = '';
		} else {
			$params = $this->params . ' ';
		}

		switch($this->type) {
			case __SITEBUILDER_JS_DEPENDENCY:
				return '<script ' . $params . 'src="' . $normalizedPath . '"></script>';
				break;
			case __SITEBUILDER_CSS_DEPENDENCY:
				return '<link rel="stylesheet" type="text/css" ' . $params . 'href="' . $normalizedPath . '">';
				break;
		}
	}

	/**
	 * Getter for the dependency type
	 *
	 * @return int
	 */
	public function getType(): int {
		return $this->type;
	}

	/**
	 * Setter for the dependency type
	 *
	 * @param int $type
	 * @return self Returns itself for chaining other functions
	 */
	private function setType(int $type): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Getter for the dependency source
	 *
	 * @return string
	 */
	public function getSource(): string {
		return $this->source;
	}

	/**
	 * Setter for the dependency source
	 *
	 * @param string $src
	 * @return self Returns itself for chaining other functions
	 */
	private function setSource(string $src): self {
		$this->source = $src;
		return $this;
	}

	/**
	 * Getter for the dependency parameters
	 *
	 * @return string
	 */
	public function getParams(): string {
		return $this->params;
	}

	/**
	 * Setter for the dependency parameters
	 *
	 * @param string $params
	 * @return self Returns itself for chaining other functions
	 */
	private function setParams(string $params): self {
		$this->params = $params;
		return $this;
	}

}

