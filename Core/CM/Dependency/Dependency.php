<?php

namespace SiteBuilder\Core\CM\Dependency;

/**
 * The Dependency class provides a convenient way for components to find and add CSS and JS
 * dependencies to the page head.
 * To use them, define an array of dependencies in the component's
 * getDependencies() method.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM\Dependency
 * @see Component::getDependencies()
 */
abstract class Dependency {
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
	 * @param string $source The source string of the dependency
	 * @param string $params Optional additional HTML attributes of the dependency
	 * @return Dependency The initialized instance
	 */
	public final static function init(string $source, string $params = ''): Dependency {
		return new static($source, $params);
	}

	/**
	 * Removes dependencies with duplicate sources from a given array
	 *
	 * @param array $dependencies The array to process
	 */
	public final static function removeDuplicates(array &$dependencies): void {
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
	public final static function getNormalizedPath(string $frameworkDirectory, string $source): string {
		// Check if source starts with '/'
		// If yes, return unedited string: Absolute path given
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
	private final function __construct(string $source, string $params) {
		$this->setSource($source);
		$this->setParams($params);
	}

	/**
	 * Builds and returns the HTML string of the dependency based on it's class, source and
	 * parameters.
	 *
	 * @return string The generated HTML string
	 */
	public abstract function getHTML(): string;

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
	 * @param string $source
	 */
	private final function setSource(string $source): void {
		$this->source = $source;
	}

	/**
	 * Getter for the dependency parameters
	 *
	 * @return string
	 */
	public final function getParams(): string {
		return $this->params;
	}

	/**
	 * Setter for the dependency parameters
	 *
	 * @param string $params
	 */
	private final function setParams(string $params): void {
		$this->params = $params;
	}

}

