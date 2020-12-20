<?php

namespace SiteBuilder\Core\CM;

use ErrorException;
use SplObjectStorage;

/**
 * <p>
 * The ContentManager class is responsible for building and managing the content of a webpage using
 * components.
 * </p>
 * <p>
 * To modify the content of your webpage:
 * </p>
 * <ol>
 * <li>Manipulate the page head content directly using the page constructor's 'head' public field
 * ($this->page()->head).</li>
 * <li>Manipulate the page body content indirectly by adding components to the ContentManager.</li>
 * </ol>
 * <p>
 * Note that ContentManager is a Singleton class, meaning only one instance of it can be initialized
 * at a time.
 * </p>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM
 * @see PageConstructor
 * @see Component
 */
class ContentManager {
	/**
	 * Static instance field for Singleton code design in PHP
	 *
	 * @var ContentManager
	 */
	private static $instance;
	/**
	 * Wether the manager was run previously
	 *
	 * @var bool
	 */
	private $isRun;
	/**
	 * The directory in which SiteBuilder itself lives, relative to the document root.
	 * Defaults to '/SiteBuilder/'
	 *
	 * @var string
	 */
	private $frameworkDirectory;
	/**
	 * An SplObjectStorage of components that have been added to the manager
	 *
	 * @var SplObjectStorage
	 */
	private $components;
	/**
	 * The page constructor, used for easily generating the resulting HTML string
	 *
	 * @var PageConstructor
	 */
	private $page;

	/**
	 * Returns an instance of ContentManager
	 *
	 * @param array $config The configuration parameters to use
	 * @return ContentManager The initialized instance
	 */
	public static function init(array $config = []): ContentManager {
		// Check if static instance field is set
		// If yes, throw error: Singleton class already initialized!
		if(isset(ContentManager::$instance)) {
			throw new ErrorException("An instance of ContentManager has already been initialized!");
		}

		ContentManager::$instance = new self($config);
		return ContentManager::$instance;
	}

	/**
	 * Constructor for the ContentManager.
	 * To get an instance of this class, use ContentManager::init().
	 * The constructor also sets the superglobal '__SiteBuilder_ContentManager' to easily get this
	 * instance.
	 *
	 * @param array $config The configuration parameters to use
	 * @see ContentManager::init()
	 */
	private function __construct(array $config) {
		$GLOBALS['__SiteBuilder_ContentManager'] = &$this;

		$this->setIsRun(false);
		$this->setFrameworkDirectory($config['frameworkDirectory'] ?? '/SiteBuilder/');
		$this->clearComponents();
		$this->setPageConstructor(PageConstructor::init($config['lang'] ?? '', $config['prettyPrint'] ?? true));
	}

	/**
	 * Runs the ContentManager, so that the added components' contents and dependencies are added to
	 * the page.
	 * In order to output the resulting HTML to the browser, call the outputToBrowser() method.
	 * Please note that this method must be called in order for the ContentManager to work.
	 *
	 * @see ContentManager::outputToBrowser()
	 */
	public function run(): void {
		// Check if the manager was run already
		// If yes, trigger warning and return: Cannot run manager multiple times
		if($this->isRun) {
			trigger_error("The content manager has already been run!", E_USER_WARNING);
			return;
		}

		// Set is run
		$this->setIsRun(true);

		// Add components to page
		foreach($this->components as $component) {
			$this->page->body .= $component->getContent();
		}

		// Dependencies
		// Add all dependencies
		$dependencies = array();
		foreach($this->components as $components) {
			$dependencies = array_merge($dependencies, $components->getDependencies());
		}

		// Get rid of duplicate dependencies
		Dependency::removeDuplicates($dependencies);

		// Sort dependencies by class
		usort($dependencies, function (Dependency $d1, Dependency $d2) {
			return get_class($d1) <=> get_class($d2);
		});

		// Add dependencies to page
		if(!empty($dependencies)) {
			$dependencyHTML = '<!-- SiteBuilder Generated Dependencies -->';
			foreach($dependencies as $dependency) {
				$dependencyHTML .= $dependency->getHTML();
			}
			$dependencyHTML .= '<!-- End SiteBuilder Generated Dependencies -->';
			$this->page->head .= $dependencyHTML;
		}
	}

	/**
	 * Outputs previously generated HTML in run() to the browser.
	 *
	 * @see ContentManager::run()
	 */
	public function outputToBrowser(): void {
		// Check if the manager was run previously
		// If no, throw error: Cannot output to browser before running!
		if($this->isRun) {
			echo $this->page->getHTML();
		} else {
			throw new ErrorException("Cannot output to the browser before running the content manager!");
		}
	}

	/**
	 * Add a StaticHTMLComponent to the manager.
	 * This is a convenience function for $this->addComponent(StaticHTMLComponent::init($html))
	 *
	 * @param string $html The HTML content of the component
	 * @return StaticHTMLComponent The initialized component
	 * @see ContentManager::addComponent()
	 * @see StaticHTMLComponent
	 */
	public function addHTML(string $html): StaticHTMLComponent {
		$component = StaticHTMLComponent::init($html);
		$this->addComponent($component);
		return $component;
	}

	/**
	 * Check if at least one of a given component class has been added to the manager
	 *
	 * @param string $class The class name to search for
	 * @return bool The boolean result
	 */
	public function hasComponents(string $class): bool {
		try {
			$this->getComponentByClass($class);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	/**
	 * Get the first added component matching a given class name
	 *
	 * @param string $class The class name to search for
	 * @return Component The first matching component, if one is found
	 */
	public function getComponentByClass(string $class): Component {
		// Search all components for one matching the given class or that is a subclass of the given
		// class
		foreach($this->components as $component) {
			if(get_class($component) === $class || is_subclass_of($component, $class)) {
				return $component;
			}
		}

		// If here, throw error: Component not found
		throw new ErrorException("The given component class name '$class' was not found!");
	}

	/**
	 * Get all added components matching a given class name
	 *
	 * @param string $class The class name to search for
	 * @return SplObjectStorage An SplObjectStorage containing the matching components
	 */
	public function getAllComponentsByClass(string $class): SplObjectStorage {
		$ret = new SplObjectStorage();

		// Search all components for one matching the given class or that is a subclass of the given
		// class
		foreach($this->components as $component) {
			if(get_class($component) === $class || is_subclass_of($component, $class)) {
				$ret->attach($component);
			}
		}

		// Check if any components were found
		// If no, throw error: No components found
		if($ret->count() === 0) {
			throw new ErrorException("No components of the given class name '$class' were found!");
		}

		return $ret;
	}

	/**
	 * Getter for the added components
	 *
	 * @return SplObjectStorage
	 * @see ContentManager::$components
	 */
	public function getAllComponents(): SplObjectStorage {
		return $this->components;
	}

	/**
	 * Add a component to the manager
	 *
	 * @param Component $component The component to add
	 */
	public function addComponent(Component $component): void {
		$this->components->attach($component);
	}

	/**
	 * Add an array of components to the manager
	 *
	 * @param Component ...$components The components to add
	 */
	public function addAllComponents(Component ...$components): void {
		foreach($components as $component) {
			$this->addComponent($component);
		}
	}

	/**
	 * Remove a component from the manager
	 *
	 * @param Component $component The component to remove
	 */
	public function removeComponent(Component $component): void {
		// Check if component has been added
		// If no, trigger warning: Component not found
		if($this->components->contains($component)) {
			$this->components->detach($component);
		} else {
			trigger_error("The given component was not found!", E_USER_WARNING);
		}
	}

	/**
	 * Remove an array of components from the manager
	 *
	 * @param Component ...$components The components to remove
	 */
	public function removeAllComponents(Component ...$components): void {
		foreach($components as $component) {
			$this->removeComponent($component);
		}
	}

	/**
	 * Remove all components matching a given class name from the manager
	 *
	 * @param string $class The class to filter out
	 */
	public function removeAllComponentsByClass(string $class): void {
		// Check if components of the given class have been added
		// If no, trigger warning: Components of given class not found
		if($this->hasComponents($class)) {
			$this->removeAllComponents($this->getComponentByClass($class));
		} else {
			trigger_error("No components of the given class '$class' were found!", E_USER_WARNING);
		}
	}

	/**
	 * Clear all components from the manager
	 */
	public function clearComponents(): void {
		$this->components = new SplObjectStorage();
	}

	/**
	 * Getter for wether the manager was run previously
	 *
	 * @return bool
	 * @see ContentManager::$isRun
	 */
	public function isRun(): bool {
		return $this->isRun;
	}

	/**
	 * Getter for wether the manager was run previously
	 *
	 * @param bool $isRun
	 * @see ContentManager::$isRun
	 */
	private function setIsRun(bool $isRun): void {
		$this->isRun = $isRun;
	}

	/**
	 * Getter for the framework directory
	 *
	 * @return string
	 * @see ContentManager::$frameworkDirectory
	 */
	public function getFrameworkDirectory(): string {
		return $this->frameworkDirectory;
	}

	/**
	 * Setter for the framework directory
	 *
	 * @param string $frameworkDirectory
	 * @see ContentManager::$frameworkDirectory
	 */
	private function setFrameworkDirectory(string $frameworkDirectory): void {
		// Normalize directory string
		$frameworkDirectory = '/' . trim($frameworkDirectory, '/') . '/';
		$this->frameworkDirectory = $frameworkDirectory;
	}

	/**
	 * Getter for the page constructor.
	 * For a convenience function with a shorter name, see ContentManager::page()
	 *
	 * @return PageConstructor
	 * @see ContentManager::page()
	 * @see ContentManager::$page
	 */
	public function getPageConstructor(): PageConstructor {
		return $this->page;
	}

	/**
	 * Getter for the page constructor.
	 * This is a convenience function for ContentManager::getPageConstructor()
	 *
	 * @return PageConstructor
	 * @see ContentManager::getPageConstructor()
	 * @see ContentManager::$page
	 */
	public function page(): PageConstructor {
		return $this->getPageConstructor();
	}

	/**
	 * Setter for the page constructor
	 *
	 * @param PageConstructor $page
	 * @see ContentManager::$page
	 */
	private function setPageConstructor(PageConstructor $page): void {
		$this->page = $page;
	}

}

