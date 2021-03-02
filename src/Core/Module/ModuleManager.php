<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Module;

use Eufony\Core\FrameworkManager;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Runnable;
use Eufony\Utils\Traits\Singleton;
use UnexpectedValueException;

final class ModuleManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private array $modules;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();
		$this->modules = array();
	}

	public function module(string $module_class): Module {
		return $this->modules[$module_class]
			// Cannot return uninitialized module
			?? throw new UnexpectedValueException("Failed while getting module: The module '$module_class' isn't initialized");
	}

	public function modules(): array {
		return $this->modules;
	}

	public function init(string $module_class, array $config = []): void {
		// Assert that the given class is a subclass of Module: ModuleManager only manages Modules
		if(!is_subclass_of($module_class, Module::class)) {
			throw new UnexpectedValueException("Failed while initializing module: The given class '$module_class' must be a module");
		}

		// Assert that the given module has not already been initialized: Cannot reinitialize Modules
		if($module_class::initialized()) {
			throw new UnexpectedValueException("Failed while initializing module: The module '$module_class' has already been initialized");
		}

		// Init module
		$module = new $module_class();
		$this->modules[$module_class] = $module;
		$module->init($config);
	}

	public function uninit(string $module_class = null): void {
		if($module_class === null) {
			array_map(fn($module) => $this->uninit($module), array_keys($this->modules));
		} else {
			// Assert that the given class is a subclass of Module: ModuleManager only manages Modules
			if(!is_subclass_of($module_class, Module::class)) {
				throw new UnexpectedValueException("Failed while uninitializing module: The given class '$module_class' must be module");
			}

			// Assert that the given module is initialized: Cannot uninitialize non-existing module
			if(!$module_class::initialized()) {
				throw new UnexpectedValueException("Failed while uninitializing module: The module '$module_class' isn't initialized");
			}

			// Uninit module
			$this->modules[$module_class]->uninit();
			unset($this->modules[$module_class]);
		}
	}

	public function runEarly(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);
		array_map(fn($module) => $module->runEarly(), $this->modules);
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);
		array_map(fn($module) => $module->run(), $this->modules);
	}

	public function runLate(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(3);
		array_map(fn($module) => $module->runLate(), $this->modules);
	}

}
