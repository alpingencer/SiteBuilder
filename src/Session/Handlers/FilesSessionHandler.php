<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Session\Handlers;

use Eufony\FileSystem\Directory;
use Eufony\FileSystem\Path;
use Eufony\Session\SessionHandler;

class FilesSessionHandler extends SessionHandler {

	public static function savePath(): string {
		$sessionsDir = '/storage/sessions';

		if(!Directory::exists($sessionsDir)) {
			Directory::make($sessionsDir);
		}
		
		return Path::full($sessionsDir);
	}

}
