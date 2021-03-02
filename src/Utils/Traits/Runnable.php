<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Traits;

use BadMethodCallException;
use UnexpectedValueException;

trait Runnable {
	private int $currentRunStage;

	public function currentRunStage(): int {
		$this->currentRunStage ??= 1;
		return $this->currentRunStage;
	}

	private function assertCurrentRunStage(int $run_stage): void {
		// Call to getter function to set run stage initially
		$current_stage = $this->currentRunStage();

		// Assert that the given run stage is greater than 0: Run stages are 1-indexed
		if($run_stage <= 0) {
			throw new UnexpectedValueException("Forbidden run stage value: Run stage must be greater than 0");
		}

		// Assert that the run stage wasn't run previously: Cannot re-run run stages
		if($this->currentRunStage > $run_stage) {
			throw new BadMethodCallException("Forbidden call to run stage #$run_stage: Already ran (currently on stage #$current_stage)");
		}

		// Assert that the run stage doesn't skip ahead: Must run run stages in order
		if($this->currentRunStage < $run_stage) {
			throw new BadMethodCallException("Forbidden call to run stage #$run_stage! Cannot skip ahead (currently on stage #$current_stage)");
		}

		// Increment to next run stage
		$this->currentRunStage++;
	}

}
