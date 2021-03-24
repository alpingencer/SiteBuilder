<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Config;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Eufony\Support\Traits\StaticOnly;
use UnexpectedValueException;

class Config {
	use StaticOnly;

	public static function setup(string $appDir) {
		// Include constants.php, if it exists
		@include_once $appDir . '/config/constants.php';

		// Assert that APP_ENV is a non-empty string
		if(defined('APP_ENV') && (!is_string(APP_ENV) || empty(APP_ENV))) {
			throw new ConfigurationException("'APP_ENV' must be a non-empty string");
		}

		// Remove all other environment variables
		// Passing environment variables through other means is deprecated
		$_ENV = [];

		// Create a mutable Dotenv repository with only an EnvConstAdapter
		$repository = RepositoryBuilder::createWithNoAdapters()->addAdapter(EnvConstAdapter::class)->make();
		$configDir = $appDir . '/config';

		// Load the default .env file if it exists
		$dotenv = Dotenv::create($repository, $configDir, '.env');
		$dotenv->safeLoad();

		// If APP_ENV is defined, load the corresponding .env file
		if(defined('APP_ENV')) {
			$dotenv = Dotenv::create($repository, $configDir, '.env.' . APP_ENV);
			$dotenv->load();
		}

		// Add the given application root directory as an environment variable
		$_ENV['APP_DIR'] = $appDir;
	}

	public static function get(string $name, bool $required = false, string|array $expected = null): mixed {
		if(!empty($_ENV[$name])) {
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

	public static function all(): array {
		return $_ENV;
	}

}
