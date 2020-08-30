<?php

namespace SiteBuilder;

use SplObjectStorage;

/**
 * The SiteBuilderFamily class determines a set of SiteBuilderComponents a SiteBuilderPage must and must not have
 * to be accepted and proccessed by a SiteBuilderSystem.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see SiteBuilderPage
 * @see SiteBuilderComponent
 * @see SiteBuilderSystem
 */
class SiteBuilderFamily {
	/**
	 * The arrays specifying the ruleset of this family
	 *
	 * @var array $all
	 * @var array $one
	 * @var array $none
	 */
	private $all, $one, $none;

	/**
	 * Return an instance of SiteBuilderFamily
	 *
	 * @return self The instantiated instance
	 */
	public static function newInstance(): self {
		return new self();
	}

	/**
	 * Constructor for SiteBuilderFamily.
	 * To get an instance of this class with chainable functions, use SiteBuilderFamily::newInstance().
	 *
	 * @see SiteBuilderFamily::newInstance()
	 */
	public function __construct() {
		$this->all = array();
		$this->one = array();
		$this->none = array();
	}

	/**
	 * Require that a page has all the given component classes
	 *
	 * @param string ...$classes The class names to require
	 * @return self Returns itself to chain other functions
	 * @see SiteBuilderFamily::requireOne(string ...$classes)
	 * @see SiteBuilderFamily::requireNone(string ...$classes)
	 * @see SiteBuilderFamily::matches(SplObjectStorage $components)
	 */
	public function requireAll(string ...$classes): self {
		$this->all = array_merge($this->all, $classes);
		return $this;
	}

	/**
	 * Require that a page has at least one of the given component classes
	 *
	 * @param string ...$classes The class names, of which at least one is required
	 * @return self Returns itself to chain other functions
	 * @see SiteBuilderFamily::requireAll(string ...$classes)
	 * @see SiteBuilderFamily::requireNone(string ...$classes)
	 * @see SiteBuilderFamily::matches(SplObjectStorage $components)
	 */
	public function requireOne(string ...$classes): self {
		$this->one = array_merge($this->one, $classes);
		return $this;
	}

	/**
	 * Require that a page has none of the given component classes
	 *
	 * @param string ...$classes The class names to exclude
	 * @return self Returns itself to chain other functions
	 * @see SiteBuilderFamily::requireAll(string ...$classes)
	 * @see SiteBuilderFamily::requireOne(string ...$classes)
	 * @see SiteBuilderFamily::matches(SplObjectStorage $components)
	 */
	public function requireNone(string ...$classes): self {
		$this->none = array_merge($this->none, $classes);
		return $this;
	}

	/**
	 * Check if the given components matches the ruleset of this family,
	 * as specified previously by the requireAll(), requireOne(), and requireNone() functions.
	 * Note the component can also be a subclass of a specified class.
	 *
	 * @param SplObjectStorage $components The components to check
	 * @return bool The boolean result
	 * @see SiteBuilderFamily::requireAll(string ...$classes)
	 * @see SiteBuilderFamily::requireOne(string ...$classes)
	 * @see SiteBuilderFamily::requireNone(string ...$classes)
	 */
	public function matches(SplObjectStorage $components): bool {
		// Check if $components contains at least one of $none (if yes, fail)
		foreach($this->none as $exclude) {
			foreach($components as $component) {
				if(get_class($component) === $exclude || is_subclass_of($component, $exclude)) {
					return false;
				}
			}
		}

		// Check if $components contains at least one of $one (if no, fail)
		$includesOne = false;

		foreach($this->one as $include) {
			foreach($components as $component) {
				if(get_class($component) === $include || is_subclass_of($component, $include)) {
					$includesOne = true;
					break 2;
				}
			}
		}

		if(!$includesOne && sizeof($this->one) > 0) {
			return false;
		}

		// Check if $components contains at least one of each in $all (if no, fail)
		foreach($this->all as $require) {
			$containsThis = false;

			foreach($components as $component) {
				if(get_class($component) === $require || is_subclass_of($component, $require)) {
					$containsThis = true;
					break 1;
				}
			}

			if(!$containsThis) {
				return false;
			}
		}

		// If all three checks pass, return true
		return true;
	}

}
