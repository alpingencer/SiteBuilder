<?php

namespace SiteBuilder\Core\Module;

use ErrorException;

/**
 * Abstract base class for Modules, managed by ModuleManager.
 * To define a module, extend this class and override the init() and uninit() methods
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\Module
 * @see ModuleManager
 * @see Module::init()
 * @see Module::uninit()
 */
abstract class Module {

	/**
	 * Constructor for the module.
	 * Note that you cannot manually create a Module instance. You must use a ModuleManager instead.
	 * Also note that this constructor is final and cannot be overridden.
	 * To define what happens when a module is initialized, use the init() method
	 *
	 * @param array $config The configuration options to be passed to the module
	 * @see Module::init()
	 */
	public final function __construct(array $config) {
		// Check if instantiated from ModuleManager
		// If no, throw error: Modules must be instantiated by a ModuleManager
		$trace = debug_backtrace();
		if(!isset($trace[1]) || !isset($trace[1]['class']) || $trace[1]['class'] !== ModuleManager::class) {
			throw new ErrorException("Modules must be initialized by a ModuleManager!");
		}

		$this->init($config);
	}

	/**
	 * Initializes the module.
	 * Define this method to determine what happens when a module is initialized.
	 *
	 * @param array $config The configuration options passed to the module
	 * @see Module::uninit()
	 */
	public abstract function init(array $config): void;

	/**
	 * Uninitializes the module.
	 * Override this method to determine what happens when a module is uninitialized.
	 *
	 * @see Module::init()
	 */
	public function uninit(): void {}

}

