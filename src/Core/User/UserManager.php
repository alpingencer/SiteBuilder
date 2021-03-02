<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\User;

use Eufony\Core\FrameworkManager;
use Eufony\Utils\Exceptions\MisconfigurationException;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Singleton;

final class UserManager {
	public const SESSION_USER_ID = 'eufony.eufony.user.id';
	public const SESSION_LAST_ACTIVITY = 'eufony.eufony.user.last-activity';
	public const CONFIG_TIMEOUT = 'eufony.eufony.user.session-timeout';

	use ManagedObject;
	use Singleton;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		// Sessions
		// Assert that PHP sessions are not disabled: The framework and its modules rely on sessions
		if(session_status() === PHP_SESSION_DISABLED) {
			throw new MisconfigurationException('Server misconfiguration: PHP sessions must be enabled');
		}

		// Start session
		session_set_cookie_params(['samesite' => 'Lax']);
		session_start();

		// User ID
		$_SESSION[UserManager::SESSION_USER_ID] ??= uniqid(more_entropy: true);

		// Session timeout
		if(isset($config[UserManager::CONFIG_TIMEOUT])) {
			$session_timeout = $config[UserManager::CONFIG_TIMEOUT];
			$last_activity = $_SESSION[UserManager::SESSION_LAST_ACTIVITY] ?? null;

			if(isset($last_activity) && (time() - $last_activity + 1) > $session_timeout) {
				$user_id = $_SESSION[UserManager::SESSION_USER_ID];
				session_unset();
				session_destroy();
				session_start();
				$_SESSION[UserManager::SESSION_USER_ID] = $user_id;
			}
		}

		$_SESSION[UserManager::SESSION_LAST_ACTIVITY] = time();
	}

}
