<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Http\Session;

use Eufony\Config\Config;
use Eufony\Config\ConfigurationException;
use Eufony\FileSystem\Directory;
use Eufony\Utils\Traits\StaticOnly;

class Session {
	use StaticOnly;

	public static function start(): void {
		// Assert that PHP sessions are not disabled
		if(session_status() === PHP_SESSION_DISABLED) {
			throw new ConfigurationException('Server misconfiguration: PHP sessions must be enabled');
		}

		// Get available session handlers from the Handlers directory
		$session_handlers = Directory::files('file://' . __DIR__ . '/Handlers');
		$session_handlers = array_map(fn($handler) => strtolower(basename($handler, 'SessionHandler.php')), $session_handlers);

		// Set session handler
		try {
			$session_handler = Config::get('SESSION_HANDLER', expected: $session_handlers) ?? 'files';
			$session_handler_class = __NAMESPACE__ . '\Handlers\\' . ucwords($session_handler) . 'SessionHandler';
		} catch(ConfigurationException) {
			throw new ConfigurationException("Unknown session handler configured");
		}

		ini_set('session.save_handler', $session_handler);
		session_save_path(call_user_func([$session_handler_class, 'savePath']));

		// Get session timeout
		$session_timeout = Config::get('SESSION_TIMEOUT', expected: 'int');

		// Set garbage collection of unused sessions
		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 100);
		if($session_timeout) ini_set('session.gc_maxlifetime', $session_timeout);

		// Start session
		$cookie_params = ['samesite' => 'Lax'];
		if($session_timeout) $cookie_params['lifetime'] = $session_timeout;
		session_set_cookie_params($cookie_params);
		session_start();

		// Server-side session timeout logic
		if($session_timeout) {
			$last_activity = Session::get('SESSION_LAST_ACTIVITY');

			if($last_activity && (time() - $last_activity + 1) > $session_timeout) {
				session_unset();
				session_destroy();
				session_start();
			}
		}

		Session::set('SESSION_LAST_ACTIVITY', time());
	}

	public static function exists(string $name): bool {
		return isset($_SESSION[$name]);
	}

	public static function get(string $name): mixed {
		return $_SESSION[$name] ?? null;
	}

	public static function set(string $name, mixed $value): void {
		$_SESSION[$name] = $value;
	}

}
