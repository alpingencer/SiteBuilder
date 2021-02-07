<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Exceptions;

use Exception;
use Throwable;

class RedirectingException extends Exception {
	private string $httpResponseCode;

	public function __construct(int $http_response_code, string $message = "", int $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->httpResponseCode = $http_response_code;
	}

	public function getHttpResponseCode(): int {
		return $this->httpResponseCode;
	}

}
