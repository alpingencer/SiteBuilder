<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core;

use Eufony\Core\Content\ContentManager;
use Eufony\Core\Exception\ExceptionManager;
use Eufony\Core\Module\ModuleManager;
use Eufony\Core\User\UserManager;
use Eufony\Core\Website\WebsiteManager;
use Eufony\Utils\Server\Config;
use Eufony\Utils\Traits\Runnable;
use Eufony\Utils\Traits\Singleton;

final class EufonyFramework {
	use Runnable;
	use Singleton;

	private ContentManager $content;
	private ExceptionManager $exception;
	private ModuleManager $module;
	private UserManager $user;
	private WebsiteManager $website;
	private bool $ready;

	public static function managers(): array {
		return array(
			EufonyFramework::instance(),
			ContentManager::instance(),
			ExceptionManager::instance(),
			ModuleManager::instance(),
			UserManager::instance(),
			WebsiteManager::instance(),
		);
	}

	public function __construct(string $appDir) {
		$this->ready = false;

		$this->assertSingleton();

		Config::setup($appDir);

		$this->exception = new ExceptionManager();
		$this->website = new WebsiteManager();
		$this->user = new UserManager();
		$this->content = new ContentManager();
		$this->module = new ModuleManager();

		$this->ready = true;
	}

	public function run(): void {
		$this->assertCurrentRunStage(1);

		$this->website->run();

		$this->module->runEarly();
		$this->module->run();
		$this->content->run();

		$this->module->runLate();
		$this->module->uninit();
		$this->content->output();
		$this->exception->redirectOnException(false);

		$this->ready = false;
	}

	public function content(): ContentManager {
		return $this->content;
	}

	public function exception(): ExceptionManager {
		return $this->exception;
	}

	public function module(): ModuleManager {
		return $this->module;
	}

	public function user(): UserManager {
		return $this->user;
	}

	public function website(): WebsiteManager {
		return $this->website;
	}

	public function ready(): bool {
		return $this->ready;
	}

}
