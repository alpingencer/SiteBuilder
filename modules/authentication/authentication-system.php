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

		if($page->hasComponents(AuthorizationComponent::class)) {
			$component = $page->getComponent(AuthorizationComponent::class);
			$userID = $this->getUserID();
			$userLevel = $this->getUserLevel($userID);
			$pageLevel = $this->getPageLevel($page);

			$_SESSION['SiteBuilder_User_ID'] = $this->getUserID();

			if($userLevel < $pageLevel) {
				// Clear page content
				$page->clearComponents();
				$page->head = '';
				$page->body = '';

				if($userLevel === 0 && !empty($component->getRedirectURL())) {
					// Show login page
					header('Location:' . $component->getRedirectURL(), true, 303);
				} else {
					if(!empty($component->getForbiddenURL())) {
						// Show custom 403 forbidden
						header('Location:' . $component->getForbiddenURL(), true, 303);
						die();
					} else {
						// Show default 403 forbidden
						http_response_code(403);
						$page->head .= '<title>403 Forbidden access</title>';
						$page->body .= '<h1>403 Forbidden access</h1><p>You do not have permission to view this file.</p>';
					}
				}
			}
		}

		if($page->hasComponents(AuthenticationElement::class)) {
			$component = $page->getComponent(AuthenticationElement::class);

			// TODO: Make required 'SiteBuilder_LoginRequest' and 'SiteBuilder_LogoutRequest' $_POST variables more obvious

			if(isset($_POST['SiteBuilder_LoginRequest'])) {
				$userID = $this->proccessLogin($page);
				if($userID !== false) {
					$_SESSION['SiteBuilder_User_IsLoggedIn'] = true;
					$_SESSION['SiteBuilder_User_ID'] = $userID;
				}
			}

			if(isset($_POST['SiteBuilder_LogoutRequest'])) {
				$success = $this->proccessLogout($page);
				if($success) {
					$_SESSION['SiteBuilder_User_IsLoggedIn'] = false;
					unset($_SESSION['SiteBuilder_User_ID']);
				}
			}

			if(!isset($_SESSION['SiteBuilder_User_IsLoggedIn']) || $_SESSION['SiteBuilder_User_IsLoggedIn'] === false) {
				// User isn't logged in
				$component->html = $this->generateLoginHTML($component);
			} else {
				// User is logged in
				$component->html = $this->generateLogoutHTML($component);
			}
		}
	}

	public function getUserID() {
		if(isset($_SESSION['SiteBuilder_User_ID'])) {
			return $_SESSION['SiteBuilder_User_ID'];
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