<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\Component;
use function SiteBuilder\normalizePathString;

class AuthorizationComponent extends Component {
	private $redirectPagePath;

	public static function newInstance(): self {
		return new self();
	}

	public function __construct() {
		$this->clearRedirectPagePath();
	}

	public function setRedirectPagePath(string $redirectPagePath): self {
		$this->redirectPagePath = normalizePathString($redirectPagePath);
		return $this;
	}

	public function clearRedirectPagePath(): self {
		$this->setRedirectPagePath('');
		return $this;
	}

	public function getRedirectPagePath(): string {
		return $this->redirectPagePath;
	}

}
