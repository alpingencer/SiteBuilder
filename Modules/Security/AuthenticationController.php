<?php

namespace SiteBuilder\Modules\Security;

/**
 * An AuthenticationController specifies how login and logout requests are processed.
 * As this can vary greatly from website to website, this class should be extended and it's abstract
 * methods should be implemented in order to use the SecurityModule.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Modules\Security
 * @see SecurityModule
 */
abstract class AuthenticationController {

	/**
	 * Constructor for the AuthenticationController.
	 * Please note that when extending this abstract class, it is convention to keep the visibility
	 * of the constructor at protected and create a public static init() method.
	 */
	protected function __construct() {}

	/**
	 * Returns the user level of a user with a given ID.
	 * Please note that if the given user ID is invalid, this function should return 0.
	 *
	 * @param int $userID The user, as specified by their ID
	 * @return int The administrative level of the user
	 */
	public abstract function getUserLevel(int $userID): int;

	/**
	 * Validates and processes a login request of a user.
	 * Please note that if the login is unsuccessful, this function should return 0.
	 *
	 * @return int The user ID of the user upon successful login
	 */
	public abstract function processLogin(): int;

	/**
	 * Validates and processes a logout request of a user.
	 * If the logout is unsuccessful, this function should return false.
	 *
	 * @return bool Wether the logout was successful
	 */
	public abstract function processLogout(): bool;

}

