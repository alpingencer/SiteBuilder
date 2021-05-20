<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\FileSystem;

use Eufony\Utils\Config\Config;

class Path {

    /**
     * Returns whether a file path starts with a forward slash.
     *
     * @param string $path
     * @return bool
     */
    public static function isAbsolute(string $path): bool {
        return str_starts_with($path, '/');
    }

    /**
     * Returns the full absolute path of a file path.
     * Absolute paths are relative to the file system root.
     * Relative paths are relative to the project root.
     *
     * @param string $path
     * @return string
     * @throws \Eufony\Utils\Config\ConfigurationException Throws an error if called before Config::setup().
     */
    public static function full(string $path): string {
        if (!Path::isAbsolute($path)) {
            // Relative paths are relative to project root
            $path = Config::get('APP_DIR', required: true) . "/$path";
        }

        return $path;
    }

    /**
     * Returns the full file path without '.' or '..' and following symbolic links.
     *
     * @param string $path
     * @return string
     * @throws IOException Throws an error if the given path does not exist.
     */
    public static function real(string $path): string {
        $realpath = realpath(Path::full($path));

        // Assert that the real path was found
        if ($realpath === false) {
            throw new IOException("Failed to get real path of '$path': Path does not exist");
        }

        return $realpath;
    }

    /**
     * Returns the sanitized file path without '.' or '..'.
     *
     * @param string $path
     * @return string
     */
    public static function sanitized(string $path): string {
        $sanitized = str_replace('\\', '/', $path);

        $parts = array_filter(explode('/', $sanitized), fn($part) => strlen($part) > 0);
        $absolutes = array();

        foreach ($parts as $part) {
            switch ($part) {
                case '.':
                    continue 2;
                case '..':
                    array_pop($absolutes);
                    break;
                default:
                    array_push($absolutes, $part);
                    break;
            }
        }

        $sanitized = implode('/', $absolutes);

        if (Path::isAbsolute($path)) {
            $sanitized = "/$sanitized";
        }

        return $sanitized;
    }

}
