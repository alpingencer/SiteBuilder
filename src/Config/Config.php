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
use ReflectionClass;
use UnexpectedValueException;

class Config {

    public static function setup(): void {
        // Get application root from composer's autoloader class
        $class_loader_reflection = new ReflectionClass(ClassLoader::class);
        $app_dir = dirname($class_loader_reflection->getFileName(), 3);

        // Variables defined by the running process take priority
        $old_env = $_ENV;
        $_ENV = [];

        // Create a mutable Dotenv repository with only an EnvConstAdapter
        $repository = RepositoryBuilder::createWithNoAdapters()->addWriter(EnvConstAdapter::class)->make();
        $config_dir = $app_dir . '/config';

        // Load the default .env file if it exists
        $dotenv = Dotenv::create($repository, $config_dir, '.env');
        $dotenv->safeLoad();

        // If APP_ENV is defined, load the corresponding .env file
        if (Config::exists('APP_ENV')) {
            $app_env = Config::get('APP_ENV', expected: 'string');
            $dotenv = Dotenv::create($repository, $config_dir, '.env.' . $app_env);
            $dotenv->load();
        }

        // Let process environment variables override variables in .env
        // Given application root directory cannot be overridden as an environment variable
        $_ENV = array_merge($_ENV, $old_env, ['APP_DIR' => $app_dir]);
        ksort($_ENV);
    }

    public static function exists(string $name): bool {
        return !empty($_ENV[$name]);
    }

    public static function get(string $name, bool $required = false, string|array $expected = null): mixed {
        if (Config::exists($name)) {
            // Configuration parameter is found
            $option = $_ENV[$name];

            if ($expected !== null) {
                if (is_string($expected)) {
                    $expected_type = $expected;
                    $filter = match ($expected_type) {
                        'string' => FILTER_DEFAULT,
                        'int', 'integer' => FILTER_VALIDATE_INT,
                        'bool', 'boolean' => FILTER_VALIDATE_BOOL,
                        'float', 'double' => FILTER_VALIDATE_FLOAT,
                        default => throw new UnexpectedValueException("Unrecognized expected type '$expected_type'")
                    };

                    $option = filter_var($option, $filter, FILTER_NULL_ON_FAILURE);

                    // Assert that the configuration parameter matches the expected type
                    if ($option === null) {
                        throw new ConfigurationException("Unexpected type for configuration parameter '$name'");
                    }
                } elseif (is_array($expected)) {
                    $expected_values = $expected;

                    // Assert that the configuration parameter matches the expected values
                    if (!in_array($option, $expected_values)) {
                        $message = "Unexpected value '$option' for configuration parameter '$name'";
                        throw new ConfigurationException($message);
                    }
                }
            }

            // Configuration parameter matches expectations
            return $option;
        } elseif (!$required) {
            // Non-required configuration parameter isn't set, return null
            return null;
        } else {
            // Required configuration parameter isn't set, throw error
            throw new ConfigurationException("Undefined configuration parameter '$name'");
        }
    }

}
