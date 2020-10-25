<?php

namespace SiteBuilder\Modules\Database;

use SiteBuilder\Core\MM\Module;
use ErrorException;

class DatabaseModule extends Module {
	private $database;

	public function init(array $config): void {
		if(!isset($config['class'])) $config['class'] = MySQLDatabaseController::class;

		$requiredConfigParams = [
				'server',
				'name',
				'user',
				'password'
		];

		// Check if each required parameters is set
		// If no, throw error: The parameter must be defined
		foreach($requiredConfigParams as $param) {
			if(!isset($config[$param])) {
				throw new ErrorException("The required configuration parameter '$param' has not been set!");
			}
		}

		// Initiate class and connect to database
		$this->database = call_user_func(array(
				$config['class'],
				'init'
		), $config['server'], $config['name'], $config['user'], $config['password']);
		$this->database->connect();
	}

	public function getDatabase(): DatabaseController {
		return $this->database;
	}

	public function db(): DatabaseController {
		return $this->getDatabase();
	}

}

