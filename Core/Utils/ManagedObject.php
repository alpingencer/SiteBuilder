<?php

namespace SiteBuilder\Core\Utils;

use ErrorException;
use ReflectionClass;

trait ManagedObject {
	private object $manager;

	public function manager(): object {
		$this->manager ??= null;
		return $this->manager;
	}

	private function setManager(?object $manager): static {
		// Check if the given argument is null
		// If yes, throw error: The given manager must be initialized
		if($manager === null) {
			$class_short_name = (new ReflectionClass($this))->getShortName();
			throw new ErrorException("The given manager for this object of class '$class_short_name' has not been initialized!");
		}

		$this->manager = $manager;
		return $this;
	}

	private function assertManagerIsset(): void {
		// Check if the manager class is null
		// If yes, throw error: Manager class has not been set
		if($this->manager() === null) {
			throw new ErrorException("Cannot assert initializer if manager class has not been set!");
		}
	}

	private function assertCallerIsManager(): void {
		$this->assertManagerIsset();

		// Get the call trace as array
		$trace = debug_backtrace();
		$iteration = 0;

		// Skip the trace until an external object has been found
		while(($trace[$iteration]['object'] ?? null) === $this) {
			$iteration++;
		}

		// The next step in the iteration will be the first external caller
		$caller = $trace[$iteration]['object'] ?? null;

		// Check if the method call was from the manager
		// If no, throw error: Object must be managed by manager
		if($caller !== $this->manager) {
			$class_short_name = (new ReflectionClass($this))->getShortName();
			$manager_short_name = (new ReflectionClass($this->manager))->getShortName();
			throw new ErrorException("The managed class '$class_short_name' must be managed by the class '$manager_short_name'!");
		}
	}
}
