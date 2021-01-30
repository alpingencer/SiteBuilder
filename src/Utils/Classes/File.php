<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes;

use FilesystemIterator;
use SiteBuilder\Core\Website\WebsiteManager;
use SiteBuilder\Utils\Traits\StaticOnly;

class File {
	use StaticOnly;

	public static function isAbsolutePath(string $path): bool {
		return substr($path, 0, 1) === '/';
	}

	public static function fullPath(string $path): string {
		if(static::isAbsolutePath($path)) {
			// Absolute path
			return WebsiteManager::appDir() . $path;
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
			"The given file path '$file' was not found!"
		);

		$file = static::fullPath($file);
		$file_contents = file_get_contents($file);

		// Assert that the file read correctly: Cannot return on unsuccessful read
		assert(
			$file_contents !== false,
			"Error while reading the file '$file'!"
		);

		return $file_contents;
	}

	public static function files(string $directory): array {
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
