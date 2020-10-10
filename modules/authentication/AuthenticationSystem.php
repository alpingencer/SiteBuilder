<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\Family;
use SiteBuilder\Page;
use SiteBuilder\System;
use ErrorException;

abstract class AuthenticationSystem extends System {

	public function __construct() {
		parent::__construct(Family::newInstance()->requireOne(AuthorizationComponent::class, AuthenticationElement::class));
	}

	public function process(Page $page): void {
		switch(session_status()) {
			case PHP_SESSION_DISABLED:
				// Sessions are disabled by the server
				throw new ErrorException('Sessions are disabled by the server, so an AuthenticationSystem cannot be used!');
				break;
			case PHP_SESSION_NONE:
				// No session has been started yet
				session_start();
				break;
			default:
				break;
		}

		// Authorization
		if($page->hasComponentsByClass(AuthorizationComponent::class)) {
			$component = $page->getComponentByClass(AuthorizationComponent::class);
			$userID = $this->getUserID();

			if($userID === -1) {
				$userLevel = 0;
			} else {
				$userLevel = $this->getUserLevel($userID);
				$_SESSION['__SiteBuilder_UserID'] = $userID;
			}

			$pageLevel = $this->getPageLevel($page);

			if($userLevel < $pageLevel) {
				// Unauthorized
				$sb = $GLOBALS['__SiteBuilder_Core'];

				if($userLevel === 0 && !empty($component->getRedirectPagePath())) {
					// Show login page
					$_SESSION['__SiteBuilder_Redirect_URI'] = $_SERVER['REQUEST_URI'];
					$sb->redirectToPage($component->getRedirectPagePath());
				} else {
					// Show 401 or 403 page
					if($sb->isErrorPagePathDefined(401)) {
						$sb->redirectToPage($sb->getErrorPagePath(401));
					} else if($sb->isErrorPagePathDefined(403)) {
						$sb->redirectToPage($sb->getErrorPagePath(403));
					} else if($sb->isErrorPagePathDefined(400)) {
						$sb->redirectToPage($sb->getErrorPagePath(400));
					} else {
						$sb->showDefaultErrorPage(401);
					}
				}
			}
		}

		// Authentication
		if($page->hasComponentsByClass(AuthenticationElement::class)) {
			$component = $page->getComponentByClass(AuthenticationElement::class);

			// TODO: Make required '__SiteBuilder_LoginRequest' and '__SiteBuilder_LogoutRequest' $_POST variables more obvious
			// TODO: Make required '-1' return value for unsuccessful login request more obvious

			if(isset($_POST['__SiteBuilder_LoginRequest'])) {
				// Login request
				$userID = $this->processLogin($page);
				if($userID !== -1) {
					$_SESSION['__SiteBuilder_UserIsLoggedIn'] = true;
					$_SESSION['__SiteBuilder_UserID'] = $userID;

					if(isset($_SESSION['__SiteBuilder_Redirect_URI'])) {
						header('Location:' . $_SESSION['__SiteBuilder_Redirect_URI'], true, 303);
						unset($_SESSION['__SiteBuilder_Redirect_URI']);
						die();
					}
				}
			}

			if(isset($_POST['__SiteBuilder_LogoutRequest'])) {
				// Logout request
				$success = $this->processLogout($page);
				if($success) {
					$_SESSION['__SiteBuilder_UserIsLoggedIn'] = false;
					unset($_SESSION['__SiteBuilder_UserID']);
				}
			}

			if(!isset($_SESSION['__SiteBuilder_UserIsLoggedIn']) || $_SESSION['__SiteBuilder_UserIsLoggedIn'] === false) {
				// User isn't logged in
				$component->setHTML($this->generateLoginHTML($component));
			} else {
				// User is logged in
				$component->setHTML($this->generateLogoutHTML($component));
			}

			$component->setDependencies($this->getHTMLDependencies());
		}
	}

	public function getUserID(): int {
		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			return $_SESSION['__SiteBuilder_UserID'];
		} else {
			return -1;
		}
	}

	protected abstract function processLogin(): int;

	protected abstract function processLogout(): bool;

	protected abstract function generateLoginHTML(): string;

	protected abstract function generateLogoutHTML(): string;

	protected abstract function getHTMLDependencies(): array;

	protected abstract function getUserLevel(int $userID): int;

	protected abstract function getPageLevel(Page $page): int;

}
