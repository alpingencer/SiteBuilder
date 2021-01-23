<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Module;

use SiteBuilder\Utils\Bundled\Traits\ManagedObject;
use SiteBuilder\Utils\Bundled\Traits\Runnable;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

abstract class Module {
	use ManagedObject;
	use Runnable;
	use Singleton;

	public final function __construct() {
		$this->setAndAssertManager(ModuleManager::class);
		$this->assertSingleton();
	}

	public function init(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);
	}

	public function runEarly(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(3);
	}

	public function runLate(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(4);
	}

	public function uninit(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(5);
		$this->resetSingleton();
	}
}
