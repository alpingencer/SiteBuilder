<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

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
		assert(
			$run_stage > 0,
			new UnexpectedValueException("Forbidden run stage value: Run stage must be greater than 0")
		);

		// Assert that the run stage wasn't run previously: Cannot re-run run stages
		assert(
			$this->currentRunStage <= $run_stage,
			new BadMethodCallException("Forbidden call to run stage #$run_stage: Already ran (currently on stage #$current_stage)")
		);

		// Assert that the run stage doesn't skip ahead: Must run run stages in order
		assert(
			$this->currentRunStage >= $run_stage,
			new BadMethodCallException("Forbidden call to run stage #$run_stage! Cannot skip ahead (currently on stage #$current_stage)")
		);

		// Increment to next run stage
		$this->currentRunStage++;
	}

}
