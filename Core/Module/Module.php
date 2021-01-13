<?php

namespace SiteBuilder\Core\Module;

use SiteBuilder\Core\Utils\ManagedObject;
use SiteBuilder\Core\Utils\Runnable;
use SiteBuilder\Core\Utils\Singleton;

class Module {
	use ManagedObject;
	use Runnable;
	use Singleton;

	public final function __construct() {
		$this->setManager(ModuleManager::instanceOrNull())->assertCallerIsManager();
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
