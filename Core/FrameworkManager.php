<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core;

use ErrorException;
use SiteBuilder\Core\Content\ContentManager;
use SiteBuilder\Core\Module\ModuleManager;
use SiteBuilder\Core\Session\SessionManager;
use SiteBuilder\Core\Website\WebsiteManager;
use SiteBuilder\Utils\Bundled\Classes\JsonDecoder;
use SiteBuilder\Utils\Bundled\Classes\Normalizer;
use SiteBuilder\Utils\Bundled\Traits\Runnable;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

final class FrameworkManager {
	use Runnable;
	use Singleton;

	private array $config;
	private ContentManager $content;
	private ModuleManager $module;
	private SessionManager $session;
	private WebsiteManager $website;

	public static function managers(): array {
		return array(
			FrameworkManager::instance(),
			ContentManager::instance(),
			ModuleManager::instance(),
			SessionManager::instance(),
			WebsiteManager::instance(),
		);
	}

	public static function config(string $option_name = null, string $expected_type = null): mixed {
		$instance = FrameworkManager::instance();

		if($option_name === null) {
			return $instance->config;
		} else {
			$option = JsonDecoder::traverse($instance->config, $option_name, '.');

			try {
				Normalizer::assertExpectedType($option, $expected_type);
			} catch(ErrorException) {
				$option_type = gettype($option);
				throw new ErrorException("Expected type '$expected_type' for the framework configuration option '$option_name', received '$option_type'!");
			}

			return $option;
		}
	}

	public function __construct() {
		$this->assertSingleton();

		$this->config = JsonDecoder::read('/sitebuilder.json');
		JsonDecoder::assertTraversable($this->config, '.');

		$this->content = new ContentManager();
		$this->module = new ModuleManager();
		$this->session = new SessionManager();
		$this->website = new WebsiteManager();
	}

	public function content(): ContentManager {
		return $this->content;
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

		$this->content->run();

		$this->module->runLate();

		$this->content->output();
	}
}
