<?php

namespace SiteBuilder\Core\MM;

use ErrorException;

/**
 * Abstract base class for Modules, managed by ModuleManager.
 * To define a module, extend this class and override its methods.
 * There are 5 stages during which a module can execute code:
 * <ol>
 * <li>When it is being constructed, as defined in the init() method</li>
 * <li>An early run, which will be called first, as defined in runEarly()</li>
 * <li>A normal run, which will be called second, as defined in run()</li>
 * <li>An late run, which will be called last, as defined in runLate()</li>
 * <li>When it is being destructed, as defined in the uninit() method</li>
 * </ol>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\MM
 * @see ModuleManager
 * @see Module::init()
 * @see Module::runEarly()
 * @see Module::run()
 * @see Module::runLate()
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
	 * Destructor for the module.
	 * Note that this destructor is final and cannot be overridden.
	 * To define what happens when a module is destructed, use the uninit() method
	 *
	 * @see Module::uninit()
	 */
	public final function __destruct() {
		$this->uninit();
	}

	/**
	 * Initializes the module.
	 * Override this method to determine what happens when a module is initialized.
	 *
	 * @param array $config The configuration options passed to the module
	 */
	public function init(array $config): void {}

	/**
	 * First stage of running the module.
	 * Override this method to determine what happens in the first run stage.
	 */
	public function runEarly(): void {}

	/**
	 * Second stage of running the module.
	 * Override this method to determine what happens in the second run stage.
	 */
	public function run(): void {}

	/**
	 * Third stage of running the module.
	 * Override this method to determine what happens in the third run stage.
	 */
	public function runLate(): void {}

	/**
	 * Uninitializes the module.
	 * Override this method to determine what happens when a module is uninitialized.
	 */
	public function uninit(): void {}

}

