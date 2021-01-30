<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use LogicException;
use ReflectionClass;

trait Singleton {
	private static ?object $instance;

	public static function initialized(): bool {
		static::$instance ??= null;
		return static::$instance !== null;
	}

	public static function instance(): object {
		// Assert Singleton is initialized: Cannot return uninitialized instance
		$class_short_name = (new ReflectionClass(static::class))->getShortName();
		assert(
			static::initialized(),
			new LogicException("Cannot access singleton class '$class_short_name' before initialization!")
		);

		return static::$instance;
	}

	private function assertSingleton(): void {
		// Assert Singleton is uninitialized: Cannot reinitialize Singleton
		$class_short_name = (new ReflectionClass($this))->getShortName();
		assert(
			!static::initialized(),
			"Cannot initialize multiple instances of the singleton class '$class_short_name'!"
		);

		// Set instance variable after assertion in constructor
		static::$instance = $this;
	}

	private function resetSingleton(): void {
		static::$instance = null;
	}
}
