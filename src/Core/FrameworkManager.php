<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core;

use SiteBuilder\Core\Content\ContentManager;
use SiteBuilder\Core\Module\ModuleManager;
use SiteBuilder\Core\Session\SessionManager;
use SiteBuilder\Core\Website\WebsiteManager;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

final class FrameworkManager {
	use Runnable;
	use Singleton;

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

	public function __construct(array $config = []) {
		$this->assertSingleton();

		$this->content = new ContentManager($config);
		$this->module = new ModuleManager($config);
		$this->session = new SessionManager($config);
		$this->website = new WebsiteManager($config);
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
