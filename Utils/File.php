<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils;

use ErrorException;
use FilesystemIterator;
use SiteBuilder\Utils\Traits\StaticOnly;

class File {
	use StaticOnly;

	public static function path(string $path, bool $from_document_root = false): string {
		$temp_file_path = str_replace('\\', '/', $path);

		$parts = array_filter(explode('/', $temp_file_path), 'strlen');
		$absolutes = array();

		foreach($parts as $part) {
			if('.' === $part) {
				continue;
			}

			if('..' === $part) {
				array_pop($absolutes);
			} else {
				array_push($absolutes, $part);
			}
		}

		$temp_file_path = implode('/', $absolutes);

		if($from_document_root) {
			if(substr($path, 0, 1) === '/') {
				// Absolute path
				$temp_file_path = $_SERVER['DOCUMENT_ROOT'] . "/$temp_file_path";
			} else {
				// Relative path
				$temp_file_path = dirname($_SERVER['SCRIPT_FILENAME']) . "/$temp_file_path";
			}
		}

		return $temp_file_path;
	}

	public static function exists(string $file): string {
		$file = static::path($file, from_document_root: true);
		return file_exists($file);
	}

	public static function read(string $file): string {
		// Check if the computed file path exists
		// If no, throw error: File not found
		if(!static::exists($file)) {
			throw new ErrorException("The given file path '$file' was not found!");
		}

		$file = static::path($file);

		// Check if the file is readable
		// If no, throw error: Failed while getting file contents
		if(($file_contents = file_get_contents($file)) === false) {
			throw new ErrorException("Error while reading the file '$file'!");
		}

		return $file_contents;
	}

	public static function files(string $directory): array {
		$directory = static::path($directory);

		$iterator = new FilesystemIterator($directory);
		$files = array();

		foreach($iterator as $file) {
			if(!$file->isFile()) {
				continue;
			}

			array_push($files, $file->getPathname());
		}

		return $files;
	}
}
