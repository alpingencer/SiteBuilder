<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Module;

use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;

abstract class Module {
	use ManagedObject;
	use Singleton;

	public final function __construct() {
		$this->setAndAssertManager(ModuleManager::class);
		$this->assertSingleton();
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
