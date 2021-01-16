<?php

namespace SiteBuilder\Utils\Traits;

use ErrorException;
use ReflectionClass;

trait StaticOnly {
	public function __construct() {
		$class_short_name = (new ReflectionClass($this))->getShortName();
		throw new ErrorException("Cannot initialize instance of the static only class '$class_short_name'!");
	}
}
