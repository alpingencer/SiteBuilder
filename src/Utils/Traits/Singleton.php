<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use BadMethodCallException;

trait Singleton {
	private static ?object $instance;

	public static function initialized(): bool {
		static::$instance ??= null;
		return static::$instance !== null;
	}

	public static function instance(): object {
		// Assert that the singleton is initialized: Cannot return uninitialized instance
		assert(
			static::initialized(),
			new BadMethodCallException("Cannot access instance of singleton class '" . static::class . "' before initialization")
		);

		return static::$instance;
	}

	private function assertSingleton(): void {
		// Assert that the singleton is uninitialized: Cannot reinitialize Singleton
		assert(
			!static::initialized(),
			new BadMethodCallException("Forbidden multiple instantiation of the singleton class '" . static::class . "'")
		);

		// Set instance variable after assertion in constructor
		static::$instance = $this;
	}

	private function resetSingleton(): void {
		static::$instance = null;
	}

}
