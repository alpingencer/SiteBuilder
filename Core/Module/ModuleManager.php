<?php

namespace SiteBuilder\Core\Module;

use ErrorException;

/**
 * <p>
 * Manages the initialization and uninitialization of Modules
 * </p>
 * <p>
 * Note that ModuleManager is a Singleton class, meaning only one instance of it can be initialized
 * at a time.
 * </p>
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\Module
 * @see Module
 */
class ModuleManager {
	/**
	 * Static instance field for Singleton code design in PHP
	 *
	 * @var ModuleManager
	 */
	private static $instance;
	/**
	 * An array of all initialized modules
	 *
	 * @var array
	 */
	private $modules;

	/**
	 * Returns an instance of ModuleManager
	 *
	 * @return ModuleManager The initialized instance
	 */
	public static function init(): ModuleManager {
		if(isset(ModuleManager::$instance)) {
			throw new ErrorException("An instance of ModuleManager has already been instantiated!");
		}

		ModuleManager::$instance = new self();
		return ModuleManager::$instance;
	}

	/**
	 * Constructor for the ModuleManager.
	 * To get an instance of this class, use ModuleManager::init().
	 * The constructor also sets the superglobal '__SiteBuilder_ModuleManager' to easily get this
	 * instance.
	 *
	 * @see ModuleManager::init()
	 */
	private function __construct() {
		$GLOBALS['__SiteBuilder_ModuleManager'] = &$this;
		$this->modules = array();
	}

	/**
	 * Check if a module of a given class has been initialized
	 *
	 * @param string $moduleClass The module class to search for
	 * @return bool The boolean result
	 */
	public function isModuleInitialized(string $moduleClass): bool {
		return isset($this->modules[$moduleClass]);
	}

	/**
	 * Initialize a module of a given class
	 *
	 * @param string $moduleClass The module class to initialize
	 * @param array $config The configuration parameters to pass to the module
	 * @return Module The initiated module instance
	 */
	public function initModule(string $moduleClass, array $config = []): Module {
		// Check if module has already been initialized
		// If yes, throw error: Only one module of each class can be initialized at a time
		if($this->isModuleInitialized($moduleClass)) {
			throw new ErrorException("A module of the given class '$moduleClass' has already been initialized!");
		}

		$module = new $moduleClass($config);
		$this->modules[$moduleClass] = $module;
		return $module;
	}

	/**
	 * Uninitailize a module of a given class
	 *
	 * @param string $moduleClass The module class to uninitailize
	 */
	public function uninitModule(string $moduleClass): void {
		// Check if module has been initialized
		// If no, throw error: Cannot uninitiate a module that hasn't been initiated
		if(!$this->isModuleInitialized($moduleClass)) {
			throw new ErrorException("No module of the given class '$moduleClass' has been initialized!");
		}

		$this->getModule($moduleClass)->uninit();
		unset($this->modules[$moduleClass]);
	}

	/**
	 * Uninitialize all active modules
	 */
	public function uninitAllModules(): void {
		foreach(array_keys($this->modules) as $moduleClass) {
			$this->uninitModule($moduleClass);
		}
	}

	/**
	 * Gets an initialized module by its class name
	 *
	 * @param string $moduleClass The module class to search for
	 * @return Module The found module
	 */
	public function getModule(string $moduleClass): Module {
		// Check if module has been initialized
		// If no, throw error: The module must be initialized first
		if(!$this->isModuleInitialized($moduleClass)) {
			throw new ErrorException("No module of the given class '$moduleClass' has been initialized!");
		}

		return $this->modules[$moduleClass];
	}

	/**
	 * Gets all initailized modules
	 *
	 * @return array An array of all active modules
	 */
	public function getAllModules(): array {
		return $this->modules;
	}

}

