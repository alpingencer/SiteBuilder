<?php

namespace SiteBuilder\Core\Module;

use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

class Module {
	use ManagedObject;
	use Runnable;
	use Singleton;

	public final function __construct() {
		$this->setAndAssertManager(ModuleManager::instanceOrNull());
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
