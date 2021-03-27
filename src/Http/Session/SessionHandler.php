<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Http\Session;

use Eufony\Utils\Traits\StaticOnly;

abstract class SessionHandler {
	use StaticOnly;

	public static abstract function savePath(): string;

}
