<?php

use Eufony\Utils\Config\Config;
use Eufony\Utils\Config\ConfigurationException;

echo "\nUnit tests for the Eufony PHP framework by Alpin Gencer.\n\n";

echo "Using php.ini file:\t\t'" . php_ini_loaded_file() . "'\n";

# Assert that the following php.ini settings are set correctly
$php_ini_settings = array(
    'xdebug.mode' => 'coverage',
);

foreach ($php_ini_settings as $setting => $expected_value) {
    if (ini_get($setting) !== $expected_value) {
        $message = "The php.ini setting '$setting' must have a value of '$expected_value'! Is the correct php.ini file used?";
        throw new ConfigurationException($message);
    }
}

echo "\n";

# Configure testing suite
Config::setup();
