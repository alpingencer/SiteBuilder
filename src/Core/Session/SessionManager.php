<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Session;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Core\Website\WebsiteManager;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;

final class SessionManager {
	public const SESSION_LAST_ACTIVITY = 'LastActivity';
	public const CONFIG_TIMEOUT = 'session.timeout';

	use ManagedObject;
	use Singleton;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		// Check if PHP sessions are disabled on the server
		// If yes, throw error: PHP sessions must be enabled
		if(session_status() === PHP_SESSION_DISABLED) {
			throw new ErrorException('PHP sessions must be enabled by the server to use the SiteBuilder framework!');
		}

		// Start session
		session_set_cookie_params(['samesite' => 'Lax']);
		session_start();

		// Session timeout logic
		$session_timeout = FrameworkManager::config(option_name: SessionManager::CONFIG_TIMEOUT, expected_type: 'integer');

		if($session_timeout !== null) {
			$last_activity = $this->get(SessionManager::SESSION_LAST_ACTIVITY, global: true);

			if(isset($last_activity) && (time() - $last_activity + 1) > $session_timeout) {
				session_unset();
				session_destroy();
				session_start();
			}
		}

		$this->set(SessionManager::SESSION_LAST_ACTIVITY, time(), global: true);
	}

	private function varName(string $var_name, bool $global = false): string {
		$subsite = $global ? 'shared' : WebsiteManager::instance()->subsite();
		return '__SiteBuilder_' . $subsite . '_' . $var_name;
	}

	public function get(string $var_name, bool $global = false): mixed {
		return $_SESSION[$this->varName($var_name, global: $global)] ?? null;
	}

	public function set(string $var_name, mixed $value, bool $global = false): void {
		$_SESSION[$this->varName($var_name, global: $global)] = $value;
	}

	public function unset(string $var_name, bool $global = false): void {
		unset($_SESSION[$this->varName($var_name, global: $global)]);
	}
}
