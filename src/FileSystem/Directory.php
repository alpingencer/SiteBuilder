<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

use Eufony\Utils\Traits\StaticOnly;
use FilesystemIterator;

class Directory {
	use StaticOnly;

	public static function exists(string $directory): bool {
		return is_dir(Path::full($directory));
	}

	public static function files(string $directory, bool $recursive = false): array {
		// Assert that the directory exists: Cannot read files if directory is not found
		if(!Directory::exists($directory)) {
			throw new IOException("Failed to read files in directory: Directory not found");
		}

		$directory = Path::full($directory);
		$iterator = new FilesystemIterator($directory);

		// Push files into array
		$files = [];

		foreach($iterator as $file_or_directory) {
			if($file_or_directory->isFile()) {
				array_push($files, $file_or_directory->getPathname());
			} else if($file_or_directory->isDir() && $recursive) {
				$files = array_merge($files, Directory::files('file://' . $file_or_directory));
			}
		}

		// Return only the array values
		return array_values($files);
	}

}
