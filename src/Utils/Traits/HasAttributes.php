<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use UnexpectedValueException;

trait HasAttributes {
	private array $attributes;

	public function attribute(string $attribute_name, string $value = null): string|self {
		if($value === null) {
			if(!isset($this->attributes[$attribute_name])) {
				throw new UnexpectedValueException("Error while fetching attribute '$attribute_name': Attribute is undefined");
			}

			return $this->attributes[$attribute_name];
		} else {
			$this->attributes[$attribute_name] = $value;
			return $this;
		}
	}

	public function attributes(array $attributes = null): array|self {
		if($attributes === null) {
			return $this->attributes;
		} else {
			$this->attributes = $attributes;
			return $this;
		}
	}

	public function clearAttributes(): self {
		$this->attributes = array();
		return $this;
	}

	public function attributesAsString(): string {
		return implode(
			' ',
			array_map(
				fn(string $attribute_name, string $attribute) => empty($attribute) ? $attribute_name : "$attribute_name=\"$attribute\"",
				array_keys($this->attributes),
				array_values($this->attributes)
			)
		);
	}

}
