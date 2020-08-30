<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\SiteBuilderComponent;

class AuthorizationComponent extends SiteBuilderComponent {
	private $redirectURL;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$this->redirectURL = '';
	}

	public function setRedirectURL(string $redirectURL): self {
		$this->redirectURL = $redirectURL;
		return $this;
	}

	public function getRedirectURL(): string {
		return $this->redirectURL;
	}

}
