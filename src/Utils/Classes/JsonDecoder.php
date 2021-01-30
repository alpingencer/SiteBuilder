<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes;

use ParseError;
use SiteBuilder\Utils\Traits\StaticOnly;

class JsonDecoder {
	use StaticOnly;

	public static function decode(string $json): array {
		$decoded_json = json_decode($json, associative: true);

		// Assert that the given JSON was successfully decoded: JSON must be valid
		assert(
			$decoded_json !== null,
			new ParseError("Error while decoding the given JSON!")
		);

		return $decoded_json;
	}

	public static function read(string $file): array {
		$file_contents = File::read($file);

		try {
			return static::decode($file_contents);
		} catch(ParseError) {
			throw new ParseError("Error while decoding the JSON file '$file'!");
		}
	}

	public static function assertTraversable(array $json, string $separator): void {
		static::assertParamTraversable($json, $separator, '');
	}

	private static function assertParamTraversable(array $json, string $separator, string $current_param): void {
		foreach($json as $param_name => $param) {
			// Assert that the param name doesn't contain the separator: Separator is reserved
			assert(
				!str_contains($param_name, $separator),
				"The JSON parameter '$param_name' cannot contain the character '$separator' in the path '$current_param'!"
			);

			// Validate child parameters
			if(is_array($param)) {
				static::assertParamTraversable($param, $separator, $current_param . $separator . $param_name);
			}
		}
	}

	public static function traverse(array $json, string $path, string $separator, string|array $group = null): mixed {
		if(is_array($group)) {
			$group = implode($separator, $group);
		}

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
}
