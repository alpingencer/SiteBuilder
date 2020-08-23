<?php

namespace SiteBuilder\Authentication;

use SiteBuilder\PageElement\PageElement;

class AuthenticationElement extends PageElement {
	public $html;

	public function __construct() {
		parent::__construct(array());
		$this->html = '';
	}

	public static function newInstance(): self {
		return new self();
	}

	public function getContent(): string {
		return $this->html;
	}

}
