<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content\Components;

use SiteBuilder\Core\Content\Component;

class StaticHTML extends Component {
	private string $content;

	public function __construct(string $content) {
		parent::__construct();
		$this->content = $content;
	}

	public function content(): string {
		return $this->content;
	}
}
