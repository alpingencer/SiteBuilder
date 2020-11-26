<?php

namespace SiteBuilder\Modules\Database;

use SiteBuilder\Core\MM\Module;
use ErrorException;

class DatabaseModule extends Module {
	private $controller;

	public function init(array $config): void {
		// Check if required configuration parameter 'controller' has been set
		// If no, throw error: A DatabaseController must be passed to the module
		if(!isset($config['controller'])) {
			throw new ErrorException("The required configuration parameter 'controller' has not been set!");
		}

		// Set controller field and connect to database
		$this->controller = $config['controller'];
		$this->controller->connect();
	}

	public function getController(): DatabaseController {
		return $this->controller;
	}

	public function db(): DatabaseController {
		return $this->getController();
	}

}

