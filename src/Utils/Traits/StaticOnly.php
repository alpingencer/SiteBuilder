<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Utils\Traits;

use LogicException;
use ReflectionClass;

trait StaticOnly {
	public function __construct() {
		$class_short_name = (new ReflectionClass($this))->getShortName();
		throw new LogicException("Cannot initialize instance of the static only class '$class_short_name'!");
	}
}
