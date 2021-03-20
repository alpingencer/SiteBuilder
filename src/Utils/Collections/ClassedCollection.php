<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Collections;

use Countable;
use InvalidArgumentException;
use Iterator;
use SplObjectStorage;
use UnexpectedValueException;

class ClassedCollection implements Countable, Iterator {
	private string $class;
	private SplObjectStorage $data;

	public function __construct(string $class) {
		$this->class = $class;
		$this->clear();
	}

	public function class(): string {
		return $this->class;
	}

	public function contains(mixed $object): bool {
		return $this->data->contains($object);
	}

	public function array(): array {
		return iterator_to_array($this, preserve_keys: false);
	}

	public function get(string $class): ClassedCollection {
		// Search all objects for one matching the given class or subclass
		$objects = array_filter($this->array(), fn($object) => is_a($object, $class));

		// Convert back to ClassedCollection
		$collection = new ClassedCollection($this->class);
		$collection->add(...$objects);

		// Return the result
		return $collection;
	}

	public function first(string $class): object {
		// Search all objects for one matching the given class or that is a subclass of the given class
		foreach($this as $object) {
			if(is_a($object, $class)) {
				return $object;
			}
		}

		// If here, throw error: Object not found
		throw new UnexpectedValueException("Failed while getting object from collection: No object of class '$class' found");
	}

	public function data(): SplObjectStorage {
		return $this->data;
	}

	public function add(object ...$objects): void {
		foreach($objects as $object) {
			// Assert that the given object matches the type of this ClassedCollection
			if(!is_a($object, $this->class)) {
				$expected_class = $this->class;
				$object_class = $object::class;
				throw new InvalidArgumentException("Failed while adding object to collection: Cannot add object of class '$object_class' to collection of '$expected_class'");
			}

			$this->data->attach($object);
		}
	}

	public function remove(object|string $object_or_class): void {
		if(is_object($object_or_class)) {
			$object = $object_or_class;

			// Assert that the given object matches the type of this ClassedCollection
			if(!is_a($object, $this->class)) {
				$expected_class = $this->class;
				$object_class = $object::class;
				throw new InvalidArgumentException("Failed while removing object from collection: Object of class '$object_class' cannot be in collection of '$expected_class'");
			}

			$this->data->detach($object);
		} else {
			$class = $object_or_class;
			array_map(fn($object) => $this->remove($object), $this->get($class)->array());
		}
	}

	public function clear(): void {
		$this->data = new SplObjectStorage();
	}

	public function count(): int {
		return count($this->data);
	}

	public function current(): object {
		return $this->data->current();
	}

	public function next(): void {
		$this->data->next();
	}

	public function key(): int {
		return $this->data->key();
	}

	public function valid(): bool {
		return $this->data->valid();
	}

	public function rewind(): void {
		$this->data->rewind();
	}

}
