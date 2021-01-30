<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use BadMethodCallException;
use Error;
use LogicException;
use ReflectionClass;
use ValueError;

trait ManagedObject {
	private ?object $manager;

	public function manager(): ?object {
		$this->manager ??= null;
		return $this->manager;
	}

	private function setManager(object|string $manager): static {
		if(is_string($manager)) {
			try {
				/** @var $manager Singleton */
				$manager = $manager::instance();
			} catch(Error) {
				throw new LogicException("The given manager class '$manager' must be a singleton class!");
			} catch(LogicException) {
				$manager_short_name = (new ReflectionClass($manager))->getShortName();
				throw new LogicException("Cannot be managed by the singleton class '$manager_short_name' before it has been initialized!");
			}
		}

		$this->manager = $manager;
		return $this;
	}

	private function setAndAssertManager(object|string $manager): void {
		$this->setManager($manager);
		$this->assertCallerIsManager();
	}

	private function assertManagerIsset(): void {
		// Assert that the manager has been set: Cannot assert caller if manager is unknown
		assert(
			$this->manager() !== null,
			new ValueError("Cannot assert manager before manager has been set!")
		);
	}

	private function assertCallerIsManager(): void {
		$this->assertManagerIsset();

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
		$class_short_name = (new ReflectionClass($this))->getShortName();
		$manager_short_name = (new ReflectionClass($this->manager))->getShortName();
		$method = $trace[$iteration - 1]['function'];
		assert(
			$caller === $this->manager,
			new BadMethodCallException("The method '$class_short_name::$method()' must be called by the manager class '$manager_short_name'!'")
		);
	}
}
