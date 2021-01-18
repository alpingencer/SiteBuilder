<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use ErrorException;
use ReflectionClass;

trait Runnable {
	private int $currentRunStage;

	public function currentRunStage(): int {
		$this->currentRunStage ??= 1;
		return $this->currentRunStage;
	}

	private function assertCurrentRunStage(int $run_stage): void {
		$this->currentRunStage();

		// Check if the given run stage was completed previously
		// If yes, throw error: Cannot re-run previous stage
		if($this->currentRunStage > $run_stage) {
			$class_short_name = (new ReflectionClass($this))->getShortName();
			$current_stage = $this->currentRunStage;
			throw new ErrorException("The given run stage #$run_stage has already been run in the class '$class_short_name', currently on run stage #$current_stage!");
		}

		// Check if the given run stage skips a stage
		// If yes, throw error: Cannot run non-sequential stage
		if($this->currentRunStage + 1 <= $run_stage) {
			$class_short_name = (new ReflectionClass($this))->getShortName();
			$current_stage = $this->currentRunStage;
			throw new ErrorException("The given run stage #$run_stage has not yet been reached in the class '$class_short_name', currently on run stage #$current_stage!");
		}

		$this->currentRunStage++;
	}
}
