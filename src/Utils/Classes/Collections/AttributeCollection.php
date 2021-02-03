<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes\Collections;

use Countable;
use Iterator;
use UnexpectedValueException;

class AttributeCollection implements Countable, Iterator {
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
				fn(string $attribute, string $value) => empty($value) ? $attribute : "$attribute=\"$value\"",
				array_keys($this->attributes),
				array_values($this->attributes)
			)
		);
	}

	public function get(string $attribute): string {
		if(!isset($this->attributes[$attribute])) {
			throw new UnexpectedValueException("Error while fetching attribute '$attribute': Attribute is undefined");
		}

		return $this->attributes[$attribute];
	}

	public function set(string $attribute, string $value = ''): self {
		$this->attributes[$attribute] = $value;
		return $this;
	}

	public function setAll(array $attributes): self {
		foreach($attributes as $attribute => $value) {
			$this->set($attribute, $value);
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
