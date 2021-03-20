<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Server;

use Eufony\Utils\Exceptions\MisconfigurationException;
use Eufony\Utils\Traits\StaticOnly;

class Config {
	use StaticOnly;

	public static function setup(string $appDir): void {
		// Define the application root directory
		define(File::CONFIG_APP_DIR, $appDir);

		// Include all PHP files in the 'config' directory
		$files = array_filter(File::files('/config'), fn($file) => str_ends_with($file, '.php'));
		array_map(fn($file) => require_once $file, $files);
	}

	public static function get(string $name, bool $required = false): mixed {
		if(defined($name)) {
			return constant($name);
		} else if($required) {
			throw new MisconfigurationException("Undefined configuration constant '$name'");
		} else {
			return null;
		}
	}

}
