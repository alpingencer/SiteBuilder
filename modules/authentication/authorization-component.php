<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\SiteBuilderComponent;

class AuthorizationComponent extends SiteBuilderComponent {
	private $redirectURL;
	private $forbiddenURL;

	public function __construct() {
		$this->redirectURL = '';
		$this->forbiddenURL = '';
	}

	public static function newInstance(): self {
		return new self();
	}

	public function setRedirectURL(string $redirectURL): self {
		$this->redirectURL = $redirectURL;
		return $this;
	}

	public function getRedirectURL(): string {
		return $this->redirectURL;
	}

	public function setForbiddenURL(string $forbiddenURL): self {
		$this->forbiddenURL = $forbiddenURL;
		return $this;
	}

	public function getForbiddenURL(): string {
		return $this->forbiddenURL;
	}

}
