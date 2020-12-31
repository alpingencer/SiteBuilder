<?php

namespace SiteBuilder\Modules\Database;

use SiteBuilder\Core\MM\Module;
use ErrorException;

/**
 * The DatabaseModule is responsible for interfacing with databases.
 * It is extendible to handle all database types by creating a new DatabaseController class.
 * In order to use this module, initiate it using the ModuleManager, giving it a 'controller'
 * configuration parameter to set what database type to use.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Database
 * @see DatabaseController
 */
class DatabaseModule extends Module {
	/**
	 * The controller responsible for interfacing with the database
	 *
	 * @var DatabaseController
	 */
	private $controller;

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\MM\Module::init()
	 */
	public function init(array $config): void {
		// Check if required configuration parameter 'controller' has been set
		// If no, throw error: A DatabaseController must be passed to the module
		if(!isset($config['controller'])) {
			throw new ErrorException("The required configuration parameter 'controller' has not been set!");
		}

		$this->setController($config['controller']);
	}

	/**
	 * Getter for the database controller.
	 * For a convenience function with a shorter name, see DatabaseModule::db()
	 *
	 * @return DatabaseController
	 * @see DatabaseModule::db()
	 * @see DatabaseModule::$controller
	 */
	public function getController(): DatabaseController {
		return $this->controller;
	}

	/**
	 * Getter for the database controller.
	 * This is a convenience function for DatabaseModule::getController()
	 *
	 * @return DatabaseController
	 * @see DatabaseModule::getController()
	 * @see DatabaseModule::$controller
	 */
	public function db(): DatabaseController {
		return $this->getController();
	}

	/**
	 * Setter for the database controller
	 *
	 * @param DatabaseController $controller
	 * @see DatabaseModule::$controller
	 */
	private function setController(DatabaseController $controller): void {
		// Set controller field and connect to database
		$this->controller = $controller;
		$this->controller->connect();
	}

}

