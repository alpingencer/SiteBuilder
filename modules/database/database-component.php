<?php

namespace SiteBuilder\Database;

use SiteBuilder\SiteBuilderComponent;

abstract class DatabaseComponent extends SiteBuilderComponent implements Database {
	private $server, $name, $user, $password;

	public static function newInstance(string $server, string $name, string $user, string $password): self {
		return new static($server, $name, $user, $password);
	}

	public function __construct(string $server, string $name, string $user, string $password) {
		$this->server = $server;
		$this->name = $name;
		$this->user = $user;
		$this->password = $password;

		$this->connect();
	}

	public function getServer(): string {
		return $this->server;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getUser(): string {
		return $this->user;
	}

	public function getPassword(): string {
		return $this->password;
	}

}