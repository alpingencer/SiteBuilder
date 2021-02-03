<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use BadMethodCallException;

trait StaticOnly {

	public function __construct() {
		throw new BadMethodCallException("Forbidden instantiation of the static only class '" . static::class . "'");
	}

}
