<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Module;

use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Singleton;

abstract class Module {
	use ManagedObject;
	use Singleton;

	public final function __construct() {
		$this->setAndAssertManager(ModuleManager::class);
		$this->assertSingleton();
	}

	public final function __destruct() {
	}

	public function init(array $config): void {
		$this->assertCallerIsManager();
	}

	public function runEarly(): void {
		$this->assertCallerIsManager();
	}

	public function run(): void {
		$this->assertCallerIsManager();
	}

	public function runLate(): void {
		$this->assertCallerIsManager();
	}

	public function uninit(): void {
		$this->assertCallerIsManager();
		$this->resetSingleton();
	}

}
