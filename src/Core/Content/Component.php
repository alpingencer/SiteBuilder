<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Utils\Traits\HasAttributes;

abstract class Component {
	use HasAttributes;

	public function __construct() {
		ContentManager::instance()->components()->add($this);
		$this->clearAttributes();
	}

	public abstract function content(): string;

}
