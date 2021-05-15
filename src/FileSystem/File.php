<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

use Eufony\Utils\Traits\StaticOnly;

class File {
    use StaticOnly;

    public static function exists(string $file): bool {
        return is_file(Path::full($file));
    }

    public static function touch(string $file): bool {
        return touch(Path::full($file));
    }

    public static function remove(string $file): bool {
        return unlink(Path::full($file));
    }

    public static function read(string $file): string {
        // Assert that the file exists: Cannot read if file not found
        if (!File::exists($file)) {
            throw new IOException("Failed to read file '$file': File not found");
        }

        $file = Path::full($file);
        $file_contents = @file_get_contents($file);

        // Assert that the file read correctly: Cannot return on unsuccessful read
        if ($file_contents === false) {
            throw new IOException("Failed to read file '$file': File is unreadable");
        }

        return $file_contents;
    }

}
