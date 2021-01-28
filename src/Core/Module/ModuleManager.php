<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Module;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Bundled\Traits\ManagedObject;
use SiteBuilder\Utils\Bundled\Traits\Runnable;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

final class ModuleManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private array $modules;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();
		$this->modules = array();
	}

	public function moduleInitialized(string $module_class): bool {
		return isset($this->modules[$module_class]);
	}

	public function module(string $module_class): Module {
		// Check if the requested module is initialized
		// If no, throw error: Cannot get uninitialized module
		if(!$this->moduleInitialized($module_class)) {
			throw new ErrorException("No module of the given class '$module_class' has been initialized!");
		}

		return $this->modules[$module_class];
	}

	public function modules(): array {
		return $this->modules;
	}

	public function init(string $module_class, array $config = []): Module {
		// Check if the given class is a subclass of Module
		// If no, throw error: ModuleManager only manages Modules
		if(!is_subclass_of($module_class, Module::class)) {
			throw new ErrorException("The given class '$module_class' must be a subclass of '" . Module::class . "'!");
		}

		$module = new $module_class($config);
		$this->modules[$module_class] = $module;
		$module->init();

		return $module;
	}

	public function uninit(string $module_class): void {
		// Check if the given module is initialized
		// If no, trigger warning: Module not found
		if(!$this->moduleInitialized($module_class)) {
			trigger_error("No module of the given class '$module_class' has been initialized, skipping uninitialization", E_USER_WARNING);
			return;
		}

		$this->modules[$module_class]->uninit();
		unset($this->modules[$module_class]);
	}

	public function uninitAll(): void {
		foreach(array_keys($this->modules) as $module_class) {
			$this->uninit($module_class);
		}
	}

	public function runEarly(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		foreach($this->modules as $module) {
			$module->runEarly();
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);

		foreach($this->modules as $module) {
			$module->run();
		}
	}

	public function runLate(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(3);

		foreach($this->modules as $module) {
			$module->runLate();
		}
	}
}
