<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;
use SplObjectStorage;
use UnexpectedValueException;

final class ContentManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageConstructor $page;
	private SplObjectStorage $components;
	private SplObjectStorage $dependencies;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->page = new PageConstructor();
		$this->clearComponents();
		$this->clearDependencies();
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		$this->page->construct($this->components, $this->dependencies);
	}

	public function output(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);

		echo $this->page->html();
	}

	public function component(string $class): Component {
		// Search all components for one matching the given class or that is a subclass of the given class
		foreach($this->components as $component) {
			if(is_a($component, $class)) {
				/** @var $component Component */
				return $component;
			}
		}

		// If here, throw error: Component not found
		throw new UnexpectedValueException("The given component class name '$class' was not found!");
	}

	public function components(string $class = null): SplObjectStorage {
		if($class === null) {
			return $this->components;
		} else {
			$components = new SplObjectStorage();

			// Search all components for one matching the given class or subclass
			foreach($this->components as $component) {
				if(is_a($component, $class)) {
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
		$this->components->detach($component);
	}

	public function removeComponents(string $class): void {
		foreach($this->components(class: $class) as $component) {
			/** @var $component Component */
			$this->removeComponent($component);
		}
	}

	public function clearComponents(): void {
		$this->components = new SplObjectStorage();
	}

	public function dependencies(string $class = null): SplObjectStorage {
		if($class === null) {
			return $this->dependencies;
		} else {
			$dependencies = new SplObjectStorage();

			// Search all components for one matching the given class or subclass
			foreach($this->dependencies as $dependency) {
				if(is_a($dependency, $class)) {
					$dependencies->attach($dependency);
				}
			}

			return $dependencies;
		}
	}

	public function addDependency(AssetDependency $dependency): void {
		$this->dependencies->attach($dependency);
	}

	public function removeDependency(AssetDependency $dependency): void {
		$this->dependencies->detach($dependency);
	}

	public function removeDependencies(string $class): void {
		foreach($this->dependencies(class: $class) as $dependency) {
			/** @var $dependency AssetDependency */
			$this->removeDependency($dependency);
		}
	}

	public function clearDependencies(): void {
		$this->dependencies = new SplObjectStorage();
	}

	public function page(): PageConstructor {
		return $this->page;
	}

	public function appendToHead(string $content): void {
		$this->page->head .= $content;
	}
}
