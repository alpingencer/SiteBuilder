<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Classes\Collections;

use Countable;
use Iterator;
use Stringable;
use UnexpectedValueException;

class AttributeCollection implements Countable, Iterator, Stringable {
	private array $attributes;
	private int $position;

	public function __construct() {
		$this->clear();
		$this->rewind();
	}

	public function __toString(): string {
		return implode(
			' ',
			array_map(
				fn(string $attribute, $value) => $value === true ? $attribute : "$attribute=\"$value\"",
				array_keys($this->attributes),
				array_values($this->attributes)
			)
		);
	}

	public function get(string $attribute): string|bool {
		if(!isset($this->attributes[$attribute])) {
			throw new UnexpectedValueException("Error while fetching attribute '$attribute': Attribute is undefined");
		}

		return $this->attributes[$attribute];
	}

	public function array(): array {
		return $this->attributes;
	}

	public function set(string|array $attribute_or_array, string $value = null): self {
		if(is_string($attribute_or_array)) {
			$attribute = $attribute_or_array;
			$this->attributes[$attribute] = $value ?? true;
		} else {
			$array = $attribute_or_array;

			foreach($array as $attribute => $value) {
				if(is_string($attribute)) {
					$this->set($attribute, $value);
				} else {
					$this->set($value);
				}
			}
		}

		return $this;
	}

	public function clear(): self {
		$this->attributes = array();
		return $this;
	}

	public function count(): int {
		return count($this->attributes);
	}

	public function current() {
		return array_values($this->attributes)[$this->position];
	}

	public function next() {
		$this->position++;
	}

	public function key() {
		return array_keys($this->attributes)[$this->position];
	}

	public function valid(): bool {
		return isset(array_keys($this->attributes)[$this->position]);
	}

	public function rewind() {
		$this->position = 0;
	}

}
