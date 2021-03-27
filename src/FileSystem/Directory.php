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

	public static function make(string $directory, int $permissions = 0777): bool {
		return mkdir(Path::full($directory), $permissions, recursive: true);
	}

	public static function remove(string $directory, bool $recursive = false): bool {
		if($recursive) {
			foreach(Directory::directories($directory) as $dir) {
				Directory::remove('file://' . $dir);
			}
		}

		foreach(Directory::files($directory) as $file) {
			File::remove('file://' . $file);
		}

		return rmdir(Path::full($directory));
	}

	public static function walk(string $directory, bool $recursive = false): array {
		// Assert that the directory exists: Cannot walk directory if directory is not found
		if(!Directory::exists($directory)) {
			throw new IOException("Failed to walk over directory: Directory not found");
		}

		$directory = Path::full($directory);
		$iterator = new FilesystemIterator($directory);

		// Push files and directories into array
		$files_and_directories = [];

		foreach($iterator as $file_or_directory) {
			array_push($files_and_directories, $file_or_directory->getPathname());

			if($file_or_directory->isDir() && $recursive) {
				array_push($files_and_directories, ...Directory::walk('file://' . $file_or_directory, recursive: true));
			}
		}

		// Return only the array values
		return array_values($files_and_directories);
	}

	public static function files(string $directory, bool $recursive = false): array {
		// Get all files and directories
		$files_and_directories = Directory::walk($directory, $recursive);

		// Filter only the files in directory
		$files = array_filter($files_and_directories, fn($file) => File::exists('file://' . $file));

		// Return only the array values
		return array_values($files);
	}

	public static function directories(string $directory, bool $recursive = false): array {
		// Get all files and directories
		$files_and_directories = Directory::walk($directory, $recursive);

		// Filter only the subdirectories in directory
		$directories = array_filter($files_and_directories, fn($dir) => Directory::exists('file://' . $dir));

		// Return only the array values
		return array_values($directories);
	}

}
