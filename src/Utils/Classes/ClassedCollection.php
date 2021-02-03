<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Classes;

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
		$array = array();

		foreach($this as $object) {
			array_push($array, $object);
		}

		return $array;
	}

	public function get(string $class): ClassedCollection {
		$objects = new ClassedCollection($this->class);

		// Search all objects for one matching the given class or subclass
		foreach($this as $object) {
			if(is_a($object, $class)) {
				$objects->add($object);
			}
		}

		return $objects;
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

	public function add(object $object): void {
		// Assert that the given object matches the type of this ClassedCollection
		$expected_class = $this->class;
		$object_class = get_class($object);
		assert(
			is_a($object, $this->class),
			new InvalidArgumentException("Failed while adding object to collection: Cannot add object of class '$object_class' to collection of '$expected_class'")
		);

		$this->data->attach($object);
	}

	public function addAll(object ...$objects): void {
		foreach($objects as $object) {
			$this->add($object);
		}
	}

	public function remove(object $object): void {
		// Assert that the given object matches the type of this ClassedCollection
		$expected_class = $this->class;
		$object_class = get_class($object);
		assert(
			is_a($object, $this->class),
			new InvalidArgumentException("Failed while removing object from collection: Object of class '$object_class' cannot be in collection of '$expected_class'")
		);

		$this->data->detach($object);
	}

	public function removeAll(string $class): void {
		foreach($this->get($class) as $object) {
			$this->remove($object);
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
