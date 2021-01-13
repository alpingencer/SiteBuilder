<?php

namespace SiteBuilder\Core;

use SiteBuilder\Core\Module\ModuleManager;
use SiteBuilder\Core\Utils\Runnable;
use SiteBuilder\Core\Utils\Singleton;

class FrameworkManager {
	use Runnable;
	use Singleton;

	private ModuleManager $module;

	public function __construct() {
		$this->assertSingleton();
		$this->module = new ModuleManager();
	}

	public function module(): ModuleManager {
		return $this->module;
	}

	public function run(): void {
		$this->module->runEarly();
		$this->module->run();
		$this->module->runLate();
	}
}
