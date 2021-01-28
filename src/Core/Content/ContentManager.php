<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Classes\Formatter;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;
use SplObjectStorage;

final class ContentManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageConstructor $page;
	private SplObjectStorage $components;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->page = new PageConstructor();
		$this->clearComponents();
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		// Add components to page
		foreach($this->components as $component) {
			$this->page->body .= $component->content();
		}

		// Dependencies
		// Add all dependencies
		$dependencies = array();
		foreach($this->components as $components) {
			$dependencies = array_merge($dependencies, $components->dependencies());
		}

		// Get rid of duplicate dependencies
		Dependency::removeDuplicates($dependencies);

		// Sort dependencies by class
		usort($dependencies, fn(Dependency $d1, Dependency $d2) => get_class($d1) <=> get_class($d2));

		// Add dependencies to page
		if(!empty($dependencies)) {
			$dependency_html = '<!-- SiteBuilder Generated Dependencies -->';

			foreach($dependencies as $dependency) {
				$dependency_html .= Formatter::doubleSpace($dependency->html());
			}

			$dependency_html .= '<!-- End SiteBuilder Generated Dependencies -->';
			$this->appendToHead($dependency_html);
		}
	}

	public function output(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);
		echo $this->page->html();
	}

	public function hasComponent(string $class): bool {
		try {
			$this->component($class);
			return true;
		} catch(ErrorException) {
			return false;
		}
	}

	public function component(string $class): Component {
		// Search all components for one matching the given class or that is a subclass of the given class
		foreach($this->components as $component) {
			if(get_class($component) === $class || is_subclass_of($component, $class)) {
				/** @var $component Component */
				return $component;
			}
		}

		// If here, throw error: Component not found
		throw new ErrorException("The given component class name '$class' was not found!");
	}

	public function components(string $class = null): SplObjectStorage {
		if($class === null) {
			return $this->components;
		} else {
			$components = new SplObjectStorage();

			// Search all components for one matching the given class or subclass
			foreach($this->components as $component) {
				if(get_class($component) === $class || is_subclass_of($component, $class)) {
					$components->attach($component);
				}
			}

			return $components;
		}
	}

	public function addComponent(Component $component): void {
		$this->components->attach($component);
	}

	public function removeComponent(Component $component): void {
		// Check if component has been added
		// If no, trigger warning: Component not found
		if($this->components->contains($component)) {
			$this->components->detach($component);
		} else {
			trigger_error("The given component was not found!", E_USER_WARNING);
		}
	}

	public function removeComponents(string $class): void {
		// Check if components of the given class have been added
		// If no, trigger warning: Components of given class not found
		if($this->hasComponent($class)) {
			foreach($this->components(class: $class) as $component) {
				/** @var $component Component */
				$this->removeComponent($component);
			}
		} else {
			trigger_error("No components of the given class '$class' were found!", E_USER_WARNING);
		}
	}

	public function clearComponents(): void {
		$this->components = new SplObjectStorage();
	}

	public function page(): PageConstructor {
		return $this->page;
	}

	public function appendToHead(string $content): void {
		$this->page->head .= $content;
	}
}
