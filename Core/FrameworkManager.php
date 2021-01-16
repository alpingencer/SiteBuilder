<?php

namespace SiteBuilder\Core;

use ErrorException;
use SiteBuilder\Core\Module\ModuleManager;
use SiteBuilder\Core\Session\SessionManager;
use SiteBuilder\Core\Website\WebsiteManager;
use SiteBuilder\Utils\JsonDecoder;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

class FrameworkManager {
	use Runnable;
	use Singleton;

	private array $config;
	private ModuleManager $module;
	private SessionManager $session;
	private WebsiteManager $website;

	public function __construct() {
		$this->assertSingleton();
		$this->config = JsonDecoder::read('/sitebuilder.json');

		$this->module = new ModuleManager();
		$this->session = new SessionManager();
		$this->website = new WebsiteManager();
	}

	public function config(string $option_name, mixed $default, string $expected_type = null): mixed {
		if(array_key_exists($option_name, $this->config)) {
			$option = $this->config[$option_name];
			$option_type = gettype($option);

			if($expected_type !== null && $option_type !== $expected_type) {
				throw new ErrorException("Expected type '$expected_type' for the framework configuration option '$option_name', received '$option_type'!");
			}

			return $option;
		} else {
			return $default;
		}
	}

	public function module(): ModuleManager {
		return $this->module;
	}

	public function session(): SessionManager {
		return $this->session;
	}

	public function website(): WebsiteManager {
		return $this->website;
	}

	public function run(): void {
		$this->assertCurrentRunStage(1);

		$this->website->run();

		$this->module->runEarly();
		$this->module->run();
		$this->module->runLate();
	}
}
