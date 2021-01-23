<?php

namespace SiteBuilder\Utils\Bundled\Classes;

use SiteBuilder\Utils\Bundled\Traits\StaticOnly;

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
}
