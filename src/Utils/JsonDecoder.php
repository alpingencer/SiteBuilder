<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils;

use AssertionError;
use Eufony\Utils\Server\File;
use Eufony\Utils\Traits\StaticOnly;
use ParseError;

class JsonDecoder {
	use StaticOnly;

	public static function decode(string $json): array {
		return json_decode($json, associative: true)
			// Assert that the given JSON was successfully decoded: JSON must be valid
			?? throw new ParseError("Failed while decoding the given JSON: JSON is invalid");
	}

	public static function read(string $file): array {
		$file_contents = File::read($file);

		try {
			return static::decode($file_contents);
		} catch(ParseError) {
			throw new ParseError("Failed while decoding the JSON file 'file': JSON is invalid");
		}
	}

	public static function traverse(array $json, string $path, string $separator, string|array $group = null): mixed {
		static::assertTraversable($json, $separator);

		if(is_array($group)) {
			$group = implode($separator, $group);
		}

		// Replace multiple consecutive separators with single one and trim separator from start and end
		$path = preg_replace('/' . preg_quote($separator, '/') . '{2,}/', $separator, $path);
		$path = trim($path, $separator);

		// Split path into segments
		$segments = explode($separator, $path);

		// Interlace segments with group separators
		if($group !== null) {
			$group = $separator . $group . $separator;
			$segments = implode($group, $segments);
			$segments = explode($separator, $segments);
		}

		// Start with JSON root
		$current = $json;

		// Traverse JSON
		foreach($segments as $segment) {
			if(isset($current[$segment])) {
				// Next segment found
				$current = $current[$segment];
			} else {
				// Next segment not found
				return null;
			}
		}

		return $current;
	}

	private static function assertTraversable(array $json, string $separator, string $current_param = ''): void {
		foreach($json as $param_name => $param) {
			// Assert that the param name doesn't contain the separator: Separator is reserved
			if(str_contains($param_name, $separator)) {
				throw new AssertionError("Failed while traversing the given JSON: Parameter '$param_name' in the path '$current_param' cannot contain the character '$separator'!");
			}

			// Validate child parameters
			if(is_array($param)) {
				$current_param .= empty($current_param) ? $param_name : $separator . $param_name;
				static::assertTraversable($param, $separator, $current_param);
			}
		}
	}

}
