<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Classes;

use Eufony\Utils\Exceptions\IOException;
use Eufony\Utils\Traits\StaticOnly;
use FilesystemIterator;

class File {
	use StaticOnly;

	public static function isAbsolutePath(string $path): bool {
		return str_starts_with($path, '/');
	}

	public static function fullPath(string $path): string {
		if(static::isAbsolutePath($path)) {
			// Absolute path
			return dirname($_SERVER['DOCUMENT_ROOT']) . $path;
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
		assert(
			static::exists($file),
			new IOException("Failed to read file '$file': File not found")
		);

		$file = static::fullPath($file);
		$file_contents = @file_get_contents($file);

		// Assert that the file read correctly: Cannot return on unsuccessful read
		assert(
			$file_contents !== false,
			new IOException("Failed to read file '$file': File is unreadable")
		);

		return $file_contents;
	}

	public static function isFile(string $path): bool {
		$path = static::fullPath($path);
		return is_file($path);
	}

	public static function isDir(string $path): bool {
		$path = static::fullPath($path);
		return is_dir($path);
	}

	public static function files(string $directory): array {
		// Assert that the directory exists: Cannot read files if directory is not found
		assert(
			static::exists($directory),
			new IOException("Failed to read files in directory: Directory not found")
		);

		// Assert that the path is a directory: Cannot read files if not a directory
		assert(
			static::isDir($directory),
			new IOException("Failed to read files in directory: Path is not a directory")
		);

		$directory = static::fullPath($directory);
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
