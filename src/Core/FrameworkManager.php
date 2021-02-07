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
use Eufony\Core\Session\SessionManager;
use Eufony\Core\Website\WebsiteManager;
use Eufony\Utils\Exceptions\MisconfigurationException;
use Eufony\Utils\Traits\Runnable;
use Eufony\Utils\Traits\Singleton;

final class FrameworkManager {
	use Runnable;
	use Singleton;

	private ContentManager $content;
	private ExceptionManager $exception;
	private ModuleManager $module;
	private SessionManager $session;
	private WebsiteManager $website;

	public static function managers(): array {
		return array(
			FrameworkManager::instance(),
			ContentManager::instance(),
			ExceptionManager::instance(),
			ModuleManager::instance(),
			SessionManager::instance(),
			WebsiteManager::instance(),
		);
	}

	public function __construct(array $config = []) {
		$this->assertSingleton();

		// Assert that the following php.ini settings are set correctly: Eufony requires these ini settings
		$php_ini_settings = array(
			'zend.assertions' => '1',
		);

		foreach($php_ini_settings as $setting => $expected_value) {
			if(ini_get($setting) !== $expected_value) {
				throw new MisconfigurationException("Server misconfiguration error: The php.ini setting '$setting' must have a value of '$expected_value'");
			}
		}

		// Set Eufony's required php.ini settings
		ini_set('assert.active', '1');
		ini_set('assert.exception', '1');

		$this->content = new ContentManager($config);
		$this->exception = new ExceptionManager($config);
		$this->module = new ModuleManager($config);
		$this->session = new SessionManager($config);
		$this->website = new WebsiteManager($config);
	}

	public function __destruct() {
		$this->run();
	}

	private function run(): void {
		$this->assertCurrentRunStage(1);

		$this->website->run();

		$this->module->runEarly();
		$this->module->run();
		$this->content->run();

		$this->module->runLate();
		$this->content->output();
		$this->module->uninitAll();
		$this->exception->restoreHandler();
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

	public function session(): SessionManager {
		return $this->session;
	}

	public function website(): WebsiteManager {
		return $this->website;
	}

}
