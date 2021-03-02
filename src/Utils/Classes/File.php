<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Classes;

use Eufony\Utils\Exceptions\IOException;
use Eufony\Utils\Exceptions\MisconfigurationException;
use Eufony\Utils\Traits\StaticOnly;
use FilesystemIterator;

class File {
	use StaticOnly;

	public static function isAbsolutePath(string $path): bool {
		return str_starts_with($path, '/');
	}

	public static function fullPath(string $path): string {
		// Assert that the constant 'APP_DIR' is defined: Eufony needs to know where the application root is
		if(!defined("APP_DIR")) {
			throw new MisconfigurationException("Undefined constant 'APP_DIR'");
		}

		if(static::isAbsolutePath($path)) {
			// Absolute path
			return APP_DIR . $path;
		} else if(str_starts_with($path, 'file://')) {
			// Full path given
			return '/' . ltrim(substr($path, 7), '/');
		} else {
			// Relative path
			return dirname($_SERVER['SCRIPT_FILENAME']) . "/$path";
		}
	}

	public static function exists(string $file): bool {
		return file_exists(static::fullPath($file));
	}

	public static function read(string $file): string {
		// Assert that the file exists: Cannot read if file not found
		if(!static::exists($file)) {
			throw new IOException("Failed to read file '$file': File not found");
		}

		$file = static::fullPath($file);
		$file_contents = @file_get_contents($file);

		// Assert that the file read correctly: Cannot return on unsuccessful read
		if($file_contents === false) {
			throw new IOException("Failed to read file '$file': File is unreadable");
		}

		return $file_contents;
	}

	public static function isFile(string $path): bool {
		return is_file(static::fullPath($path));
	}

	public static function isDir(string $path): bool {
		return is_dir(static::fullPath($path));
	}

	public static function files(string $directory): array {
		// Assert that the directory exists: Cannot read files if directory is not found
		if(!static::exists($directory)) {
			throw new IOException("Failed to read files in directory: Directory not found");
		}

		// Assert that the path is a directory: Cannot read files if not a directory
		if(!static::isDir($directory)) {
			throw new IOException("Failed to read files in directory: Path is not a directory");
		}

		$directory = static::fullPath($directory);
		$iterator = new FilesystemIterator($directory);

		// Filter out directories
		$files = array_filter(iterator_to_array($iterator), fn($file) => $file->isFile());

		// Map file info to only the path name
		$files = array_map(fn($file) => $file->getPathname(), $files);

		// Return only the array values
		return array_values($files);
	}

}
