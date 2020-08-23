<?php

namespace SiteBuilder;

/**
 * The SiteBuilderComponent class fulfills two main functions:<br>
 * <ol>
 * <li>
 * It serves as a tag for the correspondeing SiteBuilderSystems to determine if a page should be proccessed or not.
 * The systems then check if a SiteBuilderPage matches a certain SiteBuilderFamily of components and accepts or rejects
 * it accordingly.
 * </li>
 * <li>
 * It serves as a data structure to hold neccessary information for the system to proccess a page with a given component
 * type
 * </li>
 * </ol>
 *
 * To use this class, extend another class from it add an instance of it to the page using
 * SiteBuilderPage::addComponent().
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see SiteBuilderCore
 * @see SiteBuilderPage
 * @see SiteBuilderSystem
 * @see SiteBuilderFamily
 * @see SiteBuilderPage::addComponent(SiteBuilderComponent $component)
 */
abstract class SiteBuilderComponent {

	/**
	 * Constructor for SiteBuilderComponent.
	 *
	 * <strong>Please note: When extending the SiteBuilderComponent class,
	 * it is best practice to respect the following:</strong>
	 * <ol>
	 * <li>
	 * Along with the constructor, create a public static newInstance() function.
	 * This way, each component will have two ways to instantiate it:
	 * new SiteBuilderComponent and SiteBuilderComponent::newInstance()
	 * </li>
	 * <li>
	 * Do not use default parameters in the constructor / newInstance function.
	 * Instead, write a chainable setParameter($param): self function for fields with default values.
	 * </li>
	 * </ol>
	 */
	public function __construct() {}

}
