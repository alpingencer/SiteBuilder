<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\SiteBuilderFamily;
use SiteBuilder\SiteBuilderPage;
use SiteBuilder\SiteBuilderSystem;

abstract class AuthenticationSystem extends SiteBuilderSystem {

	public function __construct(int $priority = 0) {
		parent::__construct(SiteBuilderFamily::newInstance()->requireOne(AuthorizationComponent::class, AuthenticationElement::class), $priority);
	}

	public function proccess(SiteBuilderPage $page): void {
		session_start();

		if($page->hasComponent(AuthorizationComponent::class)) {
			$component = $page->getComponent(AuthorizationComponent::class);
			$userID = $this->getUserID();
			$userLevel = $this->getUserLevel($userID);
			$pageLevel = $this->getPageLevel($page);

			$_SESSION['__SiteBuilder_UserID'] = $this->getUserID();

			if($userLevel < $pageLevel) {
				// Clear page content
				$page->clearComponents();
				$page->head = '';
				$page->body = '';

				if($userLevel === 0 && !empty($component->getRedirectURL())) {
					// Show login page
					header('Location:' . $component->getRedirectURL(), true, 303);
				} else {
					$sb = $GLOBALS['__SiteBuilderCore'];
					if(empty($sb->getForbiddenHierarchyPath())) {
						// Show default 403 page
						http_response_code(403);
						$page->head .= '<title>403 Forbidden access</title>';
						$page->body .= '<h1>403 Forbidden access</h1><p>You do not have permission to view this file.</p>';
					} else {
						// Show custom 403 page
						header('Location:/?p=' . $sb->getForbiddenHierarchyPath(), true, 303);
					}
				}
			}
		}

		if($page->hasComponent(AuthenticationElement::class)) {
			$component = $page->getComponent(AuthenticationElement::class);

			// TODO: Make required '__SiteBuilder_LoginRequest' and '__SiteBuilder_LogoutRequest' $_POST variables more obvious

			if(isset($_POST['__SiteBuilder_LoginRequest'])) {
				$userID = $this->proccessLogin($page);
				if($userID !== false) {
					$_SESSION['__SiteBuilder_UserIsLoggedIn'] = true;
					$_SESSION['__SiteBuilder_UserID'] = $userID;
				}
			}

			if(isset($_POST['__SiteBuilder_LogoutRequest'])) {
				$success = $this->proccessLogout($page);
				if($success) {
					$_SESSION['__SiteBuilder_UserIsLoggedIn'] = false;
					unset($_SESSION['__SiteBuilder_UserID']);
				}
			}

			if(!isset($_SESSION['__SiteBuilder_UserIsLoggedIn']) || $_SESSION['__SiteBuilder_UserIsLoggedIn'] === false) {
				// User isn't logged in
				$component->html = $this->generateLoginHTML($component);
			} else {
				// User is logged in
				$component->html = $this->generateLogoutHTML($component);
			}
		}
	}

	public function getUserID() {
		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			return $_SESSION['__SiteBuilder_UserID'];
		} else {
			return false;
		}
	}

	protected abstract function proccessLogin();

	protected abstract function proccessLogout(): bool;

	protected abstract function generateLoginHTML(): string;

	protected abstract function generateLogoutHTML(): string;

	protected abstract function getUserLevel(int $userID): int;

	protected abstract function getPageLevel(SiteBuilderPage $page): int;

}
