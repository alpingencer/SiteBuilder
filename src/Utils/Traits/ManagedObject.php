<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Traits;

use BadMethodCallException;
use Error;
use UnexpectedValueException;

trait ManagedObject {
	private ?object $manager;

	private function manager(object|string $manager = null): ?object {
		if($manager === null) {
			return $this->manager
				// Assert that the manager has been set: Cannot return manager if not set
				?? throw new UnexpectedValueException("Cannot return manager: Manager has not been set");
		} else {
			if(is_string($manager)) {
				try {
					$manager_class = $manager;
					/** @var $manager Singleton */
					$manager = $manager::instance();
				} catch(Error) {
					throw new UnexpectedValueException("Forbidden manager class: The given class '$manager_class' must be a Singleton");
				} catch(BadMethodCallException) {
					throw new BadMethodCallException("Forbidden manager instance: The given singleton manager '$manager_class' has not been initialized");
				}
			}

			return $manager;
		}
	}

	private function setManager(object|string $manager): static {
		$this->manager = $this->manager($manager);
		return $this;
	}

	private function setAndAssertManager(object|string $manager): void {
		$this->setManager($manager);
		$this->assertCallerIsManager();
	}

	private function assertCallerIsManager(object|string $manager = null): void {
		// Get the call trace as array
		$trace = debug_backtrace();
		$iteration = 0;

		// Skip the trace until first external object is found
		while(($trace[$iteration]['object'] ?? null) === $this) {
			$iteration++;
		}

		// The next step in the iteration will be the first external caller
		$caller = $trace[$iteration]['object'] ?? null;

		// Assert that the method call was from the manager: Object must be managed by manager
		if($caller !== $this->manager($manager)) {
			$method = $trace[$iteration - 1]['function'];
			throw new BadMethodCallException("Forbidden call to method '" . static::class . "::$method()': Method must be called by the object's manager");
		}
	}

}
