<?php

namespace SiteBuilder;

/**
 * Systems are added to the Core to process a Page if it matches a certain Family.
 * If the Page does not match the Family, it is ignored.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see Core
 * @see Page
 * @see Component
 * @see Family
 */
abstract class System {
	/**
	 * The Family a Page must match to be accepted
	 *
	 * @var Family
	 */
	private $family;

	/**
	 * Returns an instance of System
	 *
	 * @return self The instantiated instance
	 * @see System::__construct()
	 */
	public static function newInstance(): self {
		return new static();
	}

	/**
	 * Constructor for the System
	 *
	 * @param Family $family The Family this System checks for
	 */
	public function __construct(Family $family) {
		$this->setFamily($family);
	}

	/**
	 * Check if a Page will be processed by this System
	 *
	 * @param Page $page The Page to be checked
	 * @return bool The boolean result
	 */
	public function accepts(Page $page): bool {
		return $page->matchesFamily($this->family);
	}

	/**
	 * Process the given Page
	 *
	 * @param Page $page
	 */
	public abstract function process(Page $page): void;

	/**
	 * Setter for $family
	 *
	 * @param Family $family
	 */
	private function setFamily(Family $family): void {
		$this->family = $family;
	}

	/**
	 * Getter for $family
	 *
	 * @return Family
	 */
	public function getFamily(): Family {
		return $this->family;
	}

}
