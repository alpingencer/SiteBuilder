<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\FileSystem;

use FilesystemIterator;

class Directory {

    /**
     * Returns whether a given path exists and is a directory.
     *
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool {
        return is_dir(Path::full($path));
    }

    /**
     * Creates the given directory path.
     *
     * @param string $path
     * @throws IOException Throws an error on failure.
     */
    public static function make(string $path): void {
        mkdir(Path::full($path), recursive: true) or throw new IOException("Failed to create directory '$path");
    }

    /**
     * Removes the given directory path recursively.
     *
     * @param string $path
     * @throws IOException Throws an error on failure.
     */
    public static function remove(string $path): void {
        // Recursively remove all subdirectories
        foreach (Directory::subdirs($path) as $dir) {
            Directory::remove($dir);
        }

        // Remove all files in directory
        foreach (Directory::files($path) as $file) {
            File::remove($file);
        }

        rmdir(Path::full($path)) or throw new IOException("Failed to remove directory '$path'");
    }

    /**
     * Returns whether a directory is empty.
     *
     * @param string $path
     * @return bool
     */
    public static function isEmpty(string $path): bool {
        return empty(Directory::list($path));
    }

    /**
     * Returns an array of all files and subdirectories in the given directory path.
     * Optionally does a recursive search.
     *
     * @param string $path
     * @param bool $recursive
     * @return array
     * @throws IOException Throws an error if the directory was not found.
     * @see \Eufony\Utils\FileSystem\Directory::files()
     * @see \Eufony\Utils\FileSystem\Directory::subdirs()
     */
    public static function list(string $path, bool $recursive = false): array {
        // Assert that the directory exists
        if (!Directory::exists($path)) {
            throw new IOException("Failed to list directory: Directory not found");
        }

        $path = Path::full($path);
        $iterator = new FilesystemIterator($path);

        // Push files and subdirectories into array
        $files_and_dirs = [];

        foreach ($iterator as $file_or_dir) {
            array_push($files_and_dirs, $file_or_dir->getPathname());

            if ($file_or_dir->isDir() && $recursive) {
                $dir = $file_or_dir->getPathname();
                array_push($files_and_dirs, ...Directory::list($dir, recursive: true));
            }
        }

        // Return only the array values
        return array_values($files_and_dirs);
    }

    /**
     * Returns an array of all files in the given directory path.
     * Optionally does a recursive search.
     *
     * @param string $path
     * @param bool $recursive
     * @return array
     * @throws IOException Throws an error if the directory was not found.
     * @see \Eufony\Utils\FileSystem\Directory::list()
     * @see \Eufony\Utils\FileSystem\Directory::subdirs()
     */
    public static function files(string $path, bool $recursive = false): array {
        // Get all files and subdirectories
        $files_and_dirs = Directory::list($path, $recursive);

        // Filter only the files in directory
        $files = array_filter($files_and_dirs, fn($file) => File::exists($file));

        // Return only the array values
        return array_values($files);
    }

    /**
     * Returns an array of all directories in the given directory path.
     * Optionally does a recursive search.
     *
     * @param string $path
     * @param bool $recursive
     * @return array
     * @throws IOException Throws an error if the directory was not found.
     * @see \Eufony\Utils\FileSystem\Directory::list()
     * @see \Eufony\Utils\FileSystem\Directory::files()
     */
    public static function subdirs(string $path, bool $recursive = false): array {
        // Get all files and subdirectories
        $files_and_dirs = Directory::list($path, $recursive);

        // Filter only the subdirectories in directory
        $dirs = array_filter($files_and_dirs, fn($dir) => Directory::exists($dir));

        // Return only the array values
        return array_values($dirs);
    }

}
