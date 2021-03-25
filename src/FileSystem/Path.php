<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\FileSystem;

use Eufony\Config\Config;
use Eufony\Utils\Traits\StaticOnly;

class Path {
	use StaticOnly;

	public static function isAbsolute(string $path): bool {
		return str_starts_with($path, '/');
	}

	public static function full(string $path): string {
		if(Path::isAbsolute($path)) {
			// Absolute path
			return Config::get('APP_DIR', required: true) . $path;
		} else if(str_starts_with($path, 'file://')) {
			// Full path given
			return '/' . ltrim(substr($path, 7), '/');
		} else {
			// Relative path
			return dirname($_SERVER['SCRIPT_FILENAME']) . "/$path";
		}
	}

}
