<?php

/**
 * Registers an autoload function so that classes are automatically loaded as they are needed.
 * Only classes that are actually in use are loaded. As such, this is technically marginally faster
 * than including every file in the SiteBuilder framework or elsewhere, however the difference is
 * negligable.
 * Please note that the autoloader assumes that the directory structure of the website matches the
 * namespace structure exactly. Otherwise, the autoloader will fail, throwing a PHP ErrorException.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 */
namespace SiteBuilder;

use ErrorException;

spl_autoload_register(function (string $class) {
	// 1. Replace '\' in class string with '/' for directories
	$classFile = str_replace('\\', '/', $class);

	// 2. Add '.php' file extension
	$classFile .= '.php';

	// 3. Make path absolute using the server document root
	$classFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $classFile;

	// Check if file exists
	// If no, throw error: Unexpected directory structure
	if(file_exists($classFile)) {
		require_once $classFile;
	} else {
		throw new ErrorException("Could not find required file '$classFile'!");
	}
});
