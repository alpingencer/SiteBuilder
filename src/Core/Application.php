<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core;

use Eufony\Config\Config;
use Eufony\Session\Session;

class Application {

	public function __construct() {
		Config::setup();
		Session::start();
	}

}
