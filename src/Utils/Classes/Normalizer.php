<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes;

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

		$path = implode('/', $absolutes);
		return $path;
	}
}
