<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use LogicException;
use ValueError;

trait Runnable {
	private int $currentRunStage;

	public function currentRunStage(): int {
		$this->currentRunStage ??= 1;
		return $this->currentRunStage;
	}

	private function assertCurrentRunStage(int $run_stage): void {
		$this->currentRunStage();

		// Assert the given run stage is greater than 0: Run stages are 1-indexed
		assert($run_stage > 0, new ValueError("The given run stage must be greater than 0!"));

		// Assert run stage wasn't run previously: Cannot re-run run stages
		$current_stage = $this->currentRunStage;
		assert($this->currentRunStage <= $run_stage, new LogicException("Cannot re-run the stage #$run_stage! Currently on stage #$current_stage"));

		// Assert run stage doesn't skip ahead: Must run run stages in order
		$current_stage = $this->currentRunStage;
		assert($this->currentRunStage >= $run_stage, new LogicException("Cannot skip to the run stage #$run_stage! Currently on stage #$current_stage"));

		$this->currentRunStage++;
	}
}
