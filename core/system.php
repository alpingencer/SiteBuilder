<?php

namespace SiteBuilder;

/**
 * SiteBuilderSystems are added to the core to proccess a SiteBuilderPage if it matches a certain SiteBuilderFamily.
 * If the page does not match the family, it is ignored.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see StieBuilderCore
 * @see SiteBuilderPage
 * @see SiteBuilderComponent
 * @see SiteBuilderFamily
 */
abstract class SiteBuilderSystem {
	/**
	 * The family a page must match to be accepted
	 *
	 * @var SiteBuilderFamily
	 */
	private $family;
	/**
	 * The priority of a system determines the order the core proccesses it.
	 * Lower priorities will be proccessed first.
	 *
	 * @var int
	 */
	private $priority;

	public static function newInstance(SiteBuilderFamily $family, int $priority = 0) {
		return new self($family, $priority);
	}

	/**
	 * Constructor for the system
	 *
	 * @param SiteBuilderCore $sb The global SiteBuilderCore instance
	 * @param SiteBuilderFamily $family The family this system checks for
	 * @param int $priority The proccess priority of this system
	 */
	public function __construct(SiteBuilderFamily $family, int $priority = 0) {
		$this->family = $family;
		$this->priority = $priority;
	}

	/**
	 * Getter for the priority
	 *
	 * @return int
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * Getter for the family
	 *
	 * @return SiteBuilderFamily
	 */
	public function getFamily(): SiteBuilderFamily {
		return $this->family;
	}

	/**
	 * Check if a page will be proccessed by this system
	 *
	 * @param SiteBuilderPage $page The page to be checked
	 * @return bool The boolean result
	 */
	public function accepts(SiteBuilderPage $page): bool {
		return $page->matchesFamily($this->family);
	}

	/**
	 * Proccess the given page
	 *
	 * @param SiteBuilderPage $page
	 */
	public abstract function proccess(SiteBuilderPage $page): void;

}
