<?php

namespace SiteBuilder\Modules\Security;

use SiteBuilder\Core\MM\Module;
use ErrorException;

class SecurityModule extends Module {
	private $getUserLevelFunction;
	private $processLoginFunction;
	private $processLogoutFunction;
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

		$requiredConfigParams = [
				'getUserLevel',
				'processLogin',
				'processLogout'
		];

		// Check if each required parameters is set
		// If no, throw error: The parameter must be defined
		foreach($requiredConfigParams as $param) {
			if(!isset($config[$param])) {
				throw new ErrorException("The required configuration parameter '$param' has not been set!");
			}
		}

		$this->setGetUserLevelFunction($config['getUserLevel']);
		$this->setProcessLoginFunction($config['processLogin']);
		$this->setProcessLogoutFunction($config['processLogout']);

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
		if(isset($_SESSION['__SiteBuilder_UserID'])) {
			$userLevel = $this->getUserLevel($_SESSION['__SiteBuilder_UserID']);
		} else {
			$userLevel = 0;
		}

		// Check if page level is greater than user level
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
			$userID = $this->processLogin();
			if($userID !== false) {
				$_SESSION['__SiteBuilder_UserIsLoggedIn'] = true;
				$_SESSION['__SiteBuilder_UserID'] = $userID;

				// Check if '__SiteBuilder_RedirectURI' session variable is set
				// If yes, redirect to previous URI
				if(isset($_SESSION['__SiteBuilder_RedirectURI'])) {
					header('Location:' . $_SESSION['__SiteBuilder_RedirectURI'], true, 303);
					unset($_SESSION['__SiteBuilder_RedirectURI']);
					die();
				}
			}
		}

		// Check if '__SiteBuilder_LogoutRequest' POST variable is set
		// If yes, process logout
		if(isset($_POST['__SiteBuilder_LogoutRequest'])) {
			$success = $this->processLogout();
			if($success) {
				$_SESSION['__SiteBuilder_UserIsLoggedIn'] = false;
				unset($_SESSION['__SiteBuilder_UserID']);
			}
		}

		// Check if user is logged in
		// If no, generate login HTML
		// If yes, generate logout HTML
		$isLoginOrLogout = !isset($_SESSION['__SiteBuilder_UserIsLoggedIn']) || $_SESSION['__SiteBuilder_UserIsLoggedIn'] === false;
		foreach($components as $component) {
			$component->setIsLoginOrLogout($isLoginOrLogout);
		}
	}

	private function getUserLevel(int $userID): int {
		return $this->getGetUserLevelFunction()($userID);
	}

	private function processLogin() {
		$return = $this->getProcessLoginFunction()();

		// Check if return type is int or false
		// If no, throw error: Return type of given process function must be of type int or false
		if(!is_int($return) && $return !== false) {
			throw new ErrorException("Return value of processLogin() must be of type int or false!");
		}

		return $return;
	}

	private function processLogout(): bool {
		return $this->getProcessLogoutFunction()();
	}

	public function getGetUserLevelFunction(): callable {
		return $this->getUserLevelFunction;
	}

	private function setGetUserLevelFunction(callable $getUserLevelFunction): self {
		$this->getUserLevelFunction = $getUserLevelFunction;
		return $this;
	}

	public function getProcessLoginFunction(): callable {
		return $this->processLoginFunction;
	}

	private function setProcessLoginFunction(callable $processLoginFunction): self {
		$this->processLoginFunction = $processLoginFunction;
		return $this;
	}

	public function getProcessLogoutFunction(): callable {
		return $this->processLogoutFunction;
	}

	private function setProcessLogoutFunction(callable $processLogoutFunction): self {
		$this->processLogoutFunction = $processLogoutFunction;
		return $this;
	}

	public function isRedirectGuestToLogin(): bool {
		return $this->isRedirectGuestToLogin;
	}

	private function setIsRedirectGuestToLogin(bool $isRedirectGuestToLogin): self {
		$this->isRedirectGuestToLogin = $isRedirectGuestToLogin;
		return $this;
	}

	public function getLoginPagePath(): string {
		return $this->loginPagePath;
	}

	private function setLoginPagePath(string $loginPagePath): self {
		$this->loginPagePath = $loginPagePath;
		$this->setIsRedirectGuestToLogin(true);
		return $this;
	}

}

