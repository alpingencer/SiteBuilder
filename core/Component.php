<?php

namespace SiteBuilder;

/**
 * The Component class fulfills two main functions:<br>
 * <ol>
 * <li>
 * It serves as a tag for the corresponding Systems to determine if a Page should be processed or not.
 * The systems then checks if a Page matches a certain Family of components and accepts or rejects
 * it accordingly.
 * </li>
 * <li>
 * It serves as a data structure to hold neccessary information for the System to process a Page with a given Component
 * type
 * </li>
 * </ol>
 *
 * To use this class, extend another class from it add an instance of it to the page using
 * Page::addComponent().
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see Core
 * @see Page
 * @see System
 * @see Family
 * @see Page::addComponent(Component $component)
 */
abstract class Component {

	/**
	 * Constructor for Component.
	 *
	 * <strong>Please note: When extending the Component class,
	 * it is best practice to respect the following:</strong>
	 * <ol>
	 * <li>
	 * Along with the constructor, create a public static newInstance(): self function.
	 * This way, each component will have two ways to instantiate it:
	 * new Component() and Component::newInstance()
	 * </li>
	 * <li>
	 * Do not use default parameters in the constructor / newInstance function.
	 * Instead, write a chainable setParameter($param): self function for fields with default values.
	 * </li>
	 * </ol>
	 */
	public function __construct() {}

}
