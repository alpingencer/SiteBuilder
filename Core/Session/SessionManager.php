<?php

namespace SiteBuilder\Core\Session;

use ErrorException;
use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;

class SessionManager {
	public const SESSION_LAST_ACTIVITY = '__SiteBuilder_LastActivity';
	public const CONFIG_TIMEOUT = 'session-timeout';

	use ManagedObject;
	use Singleton;

	public function __construct() {
		$this->setAndAssertManager(FrameworkManager::instanceOrNull());
		$this->assertSingleton();

		// Check if PHP sessions are disabled on the server
		// If yes, throw error: PHP sessions must be enabled
		if(session_status() === PHP_SESSION_DISABLED) {
			throw new ErrorException('Cannot use the SiteBuilder framework if PHP sessions are disabled by the server!');
		}

		// Start session
		session_set_cookie_params(['samesite' => 'Lax']);
		session_start();

		// Restart user session if timed out
		$session_timeout = FrameworkManager::instance()->config(static::CONFIG_TIMEOUT, null, expected_type: 'integer');
		if($session_timeout !== null) {
			if(isset($_SESSION[static::SESSION_LAST_ACTIVITY]) && (time() - $_SESSION[static::SESSION_LAST_ACTIVITY] + 1) > $session_timeout) {
				session_unset();
				session_destroy();
				session_start();
			}
		}

		$_SESSION[static::SESSION_LAST_ACTIVITY] = time();
	}
}
