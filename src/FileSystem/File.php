<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

class File {

    public static function exists(string $path): bool {
        return is_file(Path::full($path));
    }

    public static function touch(string $path): void {
        touch(Path::full($path)) or throw new IOException("Failed to create file '$path'");
    }

    public static function remove(string $path): void {
        unlink(Path::full($path)) or throw new IOException("Failed to remove file '$path'");
    }

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

    public static function write(string $path, string $content): void {
        $num_bytes = @file_put_contents(Path::full($path), $content);

        // Assert that the file opened correctly
        if ($num_bytes === false) {
            throw new IOException("Failed to write to file '$path': File is unwritable");
        }
    }

}
