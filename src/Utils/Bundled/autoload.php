<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

/**
 * Registers an autoload function so that classes are automatically loaded as they are needed.
 * Only classes that are actually in use are loaded.
 * Please note that the autoloader assumes that the directory structure of the website matches the
 * namespace structure exactly. Otherwise, the autoloader will fail, throwing a PHP ErrorException.
 *
 * @author    Alpin Gencer
 * @namespace SiteBuilder\Utils
 */

namespace SiteBuilder\Utils;

use ErrorException;

spl_autoload_register(
	function(string $class) {
		// 1. Replace '\' in class string with '/' for directories
		$class_file = str_replace('\\', '/', $class);

		// 2. Add '.php' file extension
		$class_file .= '.php';

		// 3. Make path absolute using the server document root
		$class_file = $_SERVER['DOCUMENT_ROOT'] . "/$class_file";

		// Check if file exists
		// If yes, require it
		// If no, throw error: Directory and namespace structure do not match
		if(file_exists($class_file)) {
			require_once $class_file;
		} else {
			throw new ErrorException("Could not find required file '$class_file'!");
		}
	}
);
