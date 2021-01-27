<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Bundled\Classes;

use ErrorException;
use SiteBuilder\Utils\Bundled\Traits\StaticOnly;

class JsonDecoder {
	use StaticOnly;

	public static function decode(string $json): array {
		// Check if the file contents can be decoded into an array
		// If no, throw error: File does not contain valid JSON!
		if(($decoded_json = json_decode($json, associative: true)) === null) {
			throw new ErrorException("Error while decoding the given JSON!");
		}

		return $decoded_json;
	}

	public static function read(string $file): array {
		$file_contents = File::read($file);

		try {
			return static::decode($file_contents);
		} catch(ErrorException) {
			throw new ErrorException("Error while decoding the JSON file '$file'!");
		}
	}

	public static function assertTraversable(array $json, string $separator): void {
		foreach($json as $param_name => $param) {
			static::assertParamTraversable($param, $separator, $param_name);
		}
	}

	private static function assertParamTraversable(array $json, string $separator, string $current_param): void {
		foreach($json as $param_name => $param) {
			// Check if param name contains the separator
			// If yes, throw error: Invalid parameter name
			if(str_contains($param_name, $separator)) {
				throw new ErrorException("The JSON parameter '$param_name' cannot contain the character '$separator' in the path '$current_param'!");
			}

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
			$segments = $group . implode($group, $segments);
			$segments = array_slice(explode($separator, $segments), 1);
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
