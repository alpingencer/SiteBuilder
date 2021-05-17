<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

class File {

    /**
     * Returns whether a given path exists and is a file.
     *
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool {
        return is_file(Path::full($path));
    }

    /**
     * Creates the given file path or updates its access modification time.
     *
     * @param string $path
     * @throws IOException Throws an error on failure.
     */
    public static function touch(string $path): void {
        touch(Path::full($path)) or throw new IOException("Failed to create file '$path'");
    }

    /**
     * Removes the given file path.
     *
     * @param string $path
     * @throws IOException Throws an error on failure.
     */
    public static function remove(string $path): void {
        unlink(Path::full($path)) or throw new IOException("Failed to remove file '$path'");
    }

    /**
     * Reads from the given file path and returns its contents as a string.
     *
     * @param string $path
     * @return string
     * @throws IOException Throws an error if the file is not found or is otherwise unreadable.
     */
    public static function read(string $path): string {
        // Assert that the file exists
        if (!File::exists($path)) {
            throw new IOException("Failed to read from file '$path': File not found");
        }

        $path = Path::full($path);
        $file_contents = @file_get_contents($path);

        // Assert that the file read correctly
        if ($file_contents === false) {
            throw new IOException("Failed to read from file '$path': File is unreadable");
        }

        return $file_contents;
    }

    /**
     * Writes the given string into a file path.
     * If the file does not exist, it will be created.
     *
     * @param string $path
     * @param string $content
     * @throws IOException Throws an error if the file path is unwritable.
     */
    public static function write(string $path, string $content): void {
        $num_bytes = @file_put_contents(Path::full($path), $content);

        // Assert that the file opened correctly
        if ($num_bytes === false) {
            throw new IOException("Failed to write to file '$path': File is unwritable");
        }
    }

}
