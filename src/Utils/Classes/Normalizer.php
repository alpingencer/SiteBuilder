<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Classes;

use Eufony\Utils\Traits\StaticOnly;

class Normalizer {
	use StaticOnly;

	public static function filePath(string $path): string {
		$path = str_replace('\\', '/', $path);

		$parts = array_filter(explode('/', $path), 'strlen');
		$absolutes = array();

		foreach($parts as $part) {
			switch($part) {
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

		$path = implode('/', $absolutes);
		return $path;
	}

}
