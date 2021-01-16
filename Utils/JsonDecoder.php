<?php

namespace SiteBuilder\Utils;

use ErrorException;
use SiteBuilder\Utils\Traits\StaticOnly;

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
}
