<?php

namespace SiteBuilder\Database;

use SiteBuilder\Component;

abstract class DatabaseComponent extends Component implements DatabaseInterface {
	private $server;
	private $name;
	private $user;
	private $password;
	private $loggingEnabled;
	private $logTableName;

	public static function newInstance(string $server, string $name, string $user, string $password): self {
		return new static($server, $name, $user, $password);
	}

	public function __construct(string $server, string $name, string $user, string $password) {
		$this->setServer($server);
		$this->setName($name);
		$this->setUser($user);
		$this->setPassword($password);
		$this->disableLogging();
		$this->connect($this->server, $this->name, $this->user, $this->password);
	}

	public function enableLogging($logTableName): self {
		$this->loggingEnabled = true;
		$this->logTableName = $logTableName;
		return $this;
	}

	public function disableLogging(): self {
		$this->loggingEnabled = false;
		$this->loggingEnabled = '';
		return $this;
	}

	private function setServer(string $server): self {
		$this->server = $server;
		return $this;
	}

	public function getServer(): string {
		return $this->server;
	}

	private function setName(string $name): self {
		$this->name = $name;
		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	private function setUser(string $user): self {
		$this->user = $user;
		return $this;
	}

	public function getUser(): string {
		return $this->user;
	}

	private function setPassword(string $password): self {
		$this->password = $password;
		return $this;
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function isLoggingEnabled(): bool {
		return $this->loggingEnabled;
	}

	public function getLogTableName(): string {
		return $this->logTableName;
	}

}
