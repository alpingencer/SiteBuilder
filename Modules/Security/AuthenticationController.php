<?php

namespace SiteBuilder\Modules\Security;

abstract class AuthenticationController {

	protected function __construct() {}

	public abstract function getUserLevel(int $userID): int;

	public abstract function processLogin(): int;

	public abstract function processLogout(): bool;

}
