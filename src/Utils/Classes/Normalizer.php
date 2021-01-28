<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes;

use ErrorException;
use SiteBuilder\Utils\Traits\StaticOnly;

class Normalizer {
	use StaticOnly;

	public static function filePath(string $path): string {
		$path = str_replace('\\', '/', $path);

		$parts = array_filter(explode('/', $path), 'strlen');
		$absolutes = array();

		foreach($parts as $part) {
			if($part === '.') {
				continue;
			}

			if($part === '..') {
				array_pop($absolutes);
			} else {
				array_push($absolutes, $part);
			}
		}

		return implode('/', $absolutes);
	}

	public static function assertExpectedType(mixed $variable, string $expected_type = null): void {
		if($expected_type === null) {
			return;
		}

		$variable_type = gettype($variable);

		if($variable_type !== $expected_type) {
			throw new ErrorException("Unexpected variable type '$variable_type', expected '$expected_type'!");
		}
	}
}
