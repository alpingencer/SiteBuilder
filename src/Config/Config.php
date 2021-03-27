<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Config;

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Eufony\Utils\Traits\StaticOnly;
use ReflectionClass;
use UnexpectedValueException;

class Config {
	use StaticOnly;

	public static function setup(): void {
		// Get application root from composer's autoloader class
		$class_loader_reflection = new ReflectionClass(ClassLoader::class);
		$appDir = dirname($class_loader_reflection->getFileName(), 3);

		// Include constants.php, if it exists
		@include_once $appDir . '/config/constants.php';

		// Remove all other environment variables
		// Passing environment variables through other means is not allowed
		if(!empty($_ENV)) {
			trigger_error("\$_ENV should be empty: All environment variables must be set using the .env files", E_USER_WARNING);
		}

		$_ENV = [];

		// Create a mutable Dotenv repository with only an EnvConstAdapter
		$repository = RepositoryBuilder::createWithNoAdapters()->addWriter(EnvConstAdapter::class)->make();
		$configDir = $appDir . '/config';

		// Load the default .env file if it exists
		$dotenv = Dotenv::create($repository, $configDir, '.env');
		$dotenv->safeLoad();

		// If APP_ENV is defined, load the corresponding .env file
		if(Config::exists('APP_ENV')) {
			$appEnv = Config::get('APP_ENV', expected: 'string');
			$dotenv = Dotenv::create($repository, $configDir, '.env.' . $appEnv);
			$dotenv->load();
		}

		// Add the given application root directory as an environment variable
		$_ENV = array_merge(['APP_DIR' => $appDir], $_ENV);
	}

	public static function exists(string $name): bool {
		return !empty($_ENV[$name]);
	}

	public static function get(string $name, bool $required = false, string|array $expected = null): mixed {
		if(Config::exists($name)) {
			// Configuration parameter is found
			$option = $_ENV[$name];

			if($expected !== null) {
				if(is_string($expected)) {
					$expected_type = $expected;
					$filter = match ($expected_type) {
						'string' => FILTER_DEFAULT,
						'int', 'integer' => FILTER_VALIDATE_INT,
						'bool', 'boolean' => FILTER_VALIDATE_BOOL,
						'float', 'double' => FILTER_VALIDATE_FLOAT,
						default => throw new UnexpectedValueException("Unrecognized expected type for configuration parameter")
					};

					$option = filter_var($option, $filter, FILTER_NULL_ON_FAILURE);

					// Assert that the configuration parameter matches the expected type
					if($option === null) {
						throw new ConfigurationException("Unexpected type for configuration parameter '$name'");
					}
				} else if(is_array($expected)) {
					$expected_values = $expected;

					// Assert that the configuration parameter matches the expected values
					if(!in_array($option, $expected_values)) {
						throw new ConfigurationException("Unexpected value for configuration parameter '$name'");
					}
				}
			}

			// Configuration parameter matches expectations
			return $option;
		} else {
			if(!$required) {
				// Non-required configuration parameter isn't set, return null
				return null;
			} else {
				// Required configuration parameter isn't set, throw error
				throw new ConfigurationException("Undefined configuration parameter '$name'");
			}
		}
	}

}
