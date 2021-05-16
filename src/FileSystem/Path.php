<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

use Eufony\Config\Config;

class Path {

    public static function isAbsolute(string $path): bool {
        return str_starts_with($path, '/');
    }

    public static function full(string $path): string {
        if (Path::isAbsolute($path)) {
            // Absolute path
            return $path;
        } else {
            // Relative path to project root
            return Config::get('APP_DIR', required: true) . "/$path";
        }
    }

}
