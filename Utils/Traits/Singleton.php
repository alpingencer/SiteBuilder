<?php

namespace SiteBuilder\Utils\Traits;

use ErrorException;
use ReflectionClass;

trait Singleton {
	private static ?object $instance;

	public static function initialized(): bool {
		static::$instance ??= null;
		return static::$instance !== null;
	}

	public static function instance(): object {
		// Check if Singleton class is initialized
		// If no, throw error: Cannot return uninitialized instance
		if(!static::initialized()) {
			$class_short_name = (new ReflectionClass(static::class))->getShortName();
			throw new ErrorException("The singleton class '$class_short_name' has not been initialized!");
		}

		return static::$instance;
	}

	private function assertSingleton(): void {
		// Check if Singleton class is initialized
		// If yes, throw error: Cannot reinitialize Singleton class
		if(static::initialized()) {
			$class_short_name = (new ReflectionClass($this))->getShortName();
			throw new ErrorException("Cannot initialize multiple instances of the singleton class '$class_short_name'!");
		}

		static::$instance = $this;
	}

	private function resetSingleton(): void {
		static::$instance = null;
	}
}
