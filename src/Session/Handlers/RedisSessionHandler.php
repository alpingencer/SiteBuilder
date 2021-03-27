<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Session\Handlers;

use Eufony\Config\Config;
use Eufony\Session\SessionHandler;

class RedisSessionHandler extends SessionHandler {

	public static function savePath(): string {
		$redis_host = Config::get('REDIS_HOST', required: true);
		$redis_port = Config::get('REDIS_PORT', required: true);
		$redis_password = Config::get('REDIS_PASSWORD', required: false);

		$save_path = "tcp://$redis_host:$redis_port";
		if($redis_password !== null) $save_path .= "?auth=$redis_password";

		return $save_path;
	}

}
