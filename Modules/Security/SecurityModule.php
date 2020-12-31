<?php

namespace SiteBuilder\Modules\Security;

use SiteBuilder\Core\MM\Module;
use ErrorException;

class SecurityModule extends Module {
	private $controller;
	private $isRedirectGuestToLogin;
	private $loginPagePath;

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\MM\Module::init()
	 */
	public function init(array $config): void {
		// Check if website manager has been initialized
		// If no, throw error: The security module depends on the website manager
		if(!isset($GLOBALS['__SiteBuilder_WebsiteManager'])) {
			throw new ErrorException("TranslationModule cannot be used if a WebsiteManager has not been initialized!");
		}

		// Check if content manager has been initialized
		// If no, throw error: The security module depends on the content manager
		if(!isset($GLOBALS['__SiteBuilder_ContentManager'])) {
			throw new ErrorException("SecurityModule cannot be used if a ContentManager has not been initialized!");
		}

		// Check if 'timeout' configuration parameter is set
		// If yes, check if last login has timed out and set last activity
		// If yes, destroy current session
		if(isset($config['timeout'])) {
			if(isset($_SESSION['__SiteBuilder_UserLastActivity']) && (time() - $_SESSION['__SiteBuilder_UserLastActivity']) > $config['timeout']) {
				session_unset();
				session_destroy();
			}

			$_SESSION['__SiteBuilder_UserLastActivity'] = time();
		}

		// Start PHP session
		switch(session_status()) {
			case PHP_SESSION_DISABLED:
				// Sessions are disabled by the server
				throw new ErrorException('SecurityModule cannot be used if PHP sessions are disabled on the server!');
				break;
			case PHP_SESSION_NONE:
				// No session has been started yet
				session_start();
				break;
		}

		// Check if each required 'controller' parameters is set
		// If no, throw error: An AuthenticationController must be passed to the module
		if(!isset($config['controller'])) {
			throw new ErrorException("The required configuration parameter 'controller' has not been set!");
		}

		$this->setController($config['controller']);

		// Check if 'loginPagePath' configuration parameter is set
		// If yes, set login page path
		// If no, check if page path 'login' is defined in the hierarchy
		// If yes, set login page path to SiteBuilder default login page path
		// If no, set don't redirect on guest user
		if(isset($config['loginPagePath'])) {
			$this->setLoginPagePath($config['loginPagePath']);
		} else {
			$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];

			if($wm->getHierarchy()->isPageDefined('login')) {
				$this->setLoginPagePath('login');
			} else {
				$this->setIsRedirectGuestToLogin(false);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\MM\Module::runEarly()
	 */
	public function runEarly(): void {
		$this->authorize();
		$this->authenticate();
	}

	private function authorize(): void {
		$wm = $GLOBALS['__SiteBuilder_WebsiteManager'];

		// Get page level
		if($wm->getHierarchy()->isPageAttributeDefined($wm->getCurrentPagePath(), 'level')) {
			$pageLevel = $wm->getHierarchy()->getPageAttribute($wm->getCurrentPagePath(), 'level');
		} else {
			$pageLevel = 0;
		}

		// Get user level
		if($this->isUserLoggedIn()) {
			$userLevel = $this->controller->getUserLevel($_SESSION['__SiteBuilder_UserID']);
		} else {
			$userLevel = 0;
		}

		$_SESSION['__SiteBuilder_UserLevel'] = $userLevel;

		// Check if page level is greater than user level
		// If yes, don't show page
		if($pageLevel > $userLevel) {
			if($userLevel === 0 && $this->isRedirectGuestToLogin) {
				// User is not logged in and login page specified
				$_SESSION['__SiteBuilder_RedirectURI'] = $_SERVER['REQUEST_URI'];
				$wm->redirectToPage($this->loginPagePath);
			} else if($userLevel === 0) {
				// User is not logged in, show 401 page
				$wm->showErrorPage(401, 403, 400);
			} else {
				// User is logged in but doesn't have clearance, show 403
				$wm->showErrorPage(403, 400);
			}
		}
	}

	private function authenticate(): void {
		$cm = $GLOBALS['__SiteBuilder_ContentManager'];

		// Check if page has any authentication components
		// If no, return: Nothing to do
		if(!$cm->hasComponents(AuthenticationComponent::class)) {
			return;
		}

		$components = $cm->getAllComponentsByClass(AuthenticationComponent::class);

		// Check if '__SiteBuilder_LoginRequest' POST variable is set
		// If yes, process login
		if(isset($_POST['__SiteBuilder_LoginRequest'])) {
			$userID = $this->controller->processLogin();

			if($userID !== 0) {
				$_SESSION['__SiteBuilder_UserIsLoggedIn'] = true;
				$_SESSION['__SiteBuilder_UserID'] = $userID;

				// Check if '__SiteBuilder_RedirectURI' session variable is set
				// If yes, redirect to previous URI
				if(isset($_SESSION['__SiteBuilder_RedirectURI'])) {
					header('Location:' . $_SESSION['__SiteBuilder_RedirectURI'], true, 303);
					unset($_SESSION['__SiteBuilder_RedirectURI']);
				} else {
					header('Refresh:0');
				}

				die();
			}
		}

		// Check if '__SiteBuilder_LogoutRequest' POST variable is set
		// If yes, process logout
		if(isset($_POST['__SiteBuilder_LogoutRequest'])) {
			$success = $this->controller->processLogout();

			if($success) {
				$_SESSION['__SiteBuilder_UserIsLoggedIn'] = false;
				unset($_SESSION['__SiteBuilder_UserID']);
				header('Refresh:0');
				die();
			}
		}

		// Check if user is logged in
		// If no, generate login HTML
		// If yes, generate logout HTML
		foreach($components as $component) {
			$component->setIsLoginOrLogout(!$this->isUserLoggedIn());
		}
	}

	public function isUserLoggedIn(): bool {
		return $_SESSION['__SiteBuilder_UserIsLoggedIn'] ?? false;
	}

	public function getCurrentUserID(): int {
		// Check if user is logged in
		// If no, throw error: Cannot get user ID if user is not logged in
		if(!$this->isUserLoggedIn()) {
			throw new ErrorException("No user is currently logged in!");
		}

		return $_SESSION['__SiteBuilder_UserID'];
	}

	public function getCurrentUserLevel(): int {
		return $_SESSION['__SiteBuilder_UserLevel'];
	}

	public function getController(): AuthenticationController {
		return $this->controller;
	}

	private function setController(AuthenticationController $controller): void {
		$this->controller = $controller;
	}

	public function isRedirectGuestToLogin(): bool {
		return $this->isRedirectGuestToLogin;
	}

	private function setIsRedirectGuestToLogin(bool $isRedirectGuestToLogin): void {
		$this->isRedirectGuestToLogin = $isRedirectGuestToLogin;
	}

	public function getLoginPagePath(): string {
		return $this->loginPagePath;
	}

	private function setLoginPagePath(string $loginPagePath): void {
		$this->loginPagePath = $loginPagePath;
		$this->setIsRedirectGuestToLogin(true);
	}

}

