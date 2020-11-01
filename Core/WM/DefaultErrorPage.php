<?php

namespace SiteBuilder\Core\WM;

use ErrorException;

/**
 * A convenience class for easily generating HTML pages corresponding to HTTP error codes
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\WM
 */
class DefaultErrorPage {
	/**
	 * The HTTP error code of the page
	 *
	 * @var int
	 */
	private $errorCode;
	/**
	 * The HTTP error name of the error code
	 *
	 * @var string
	 */
	private $errorName;
	/**
	 * The message to display on the page
	 *
	 * @var string
	 */
	private $errorMessage;

	/**
	 * Returns an instance of DefaultErrorPage
	 *
	 * @param int $errorCode The error code of the page
	 * @return DefaultErrorPage The initialized instance
	 */
	public static function init(int $errorCode): DefaultErrorPage {
		return new self($errorCode);
	}

	/**
	 * Constructor for the DefaultErrorPage.
	 * To get an instance of this class, use DefaultErrorPage::init().
	 *
	 * @param int $errorCode The error code of the page
	 * @see DefaultErrorPage::init()
	 */
	private function __construct(int $errorCode) {
		$this->setErrorCode($errorCode);

		switch($this->errorCode) {
			case 400:
				$errorName = "Bad Request";
				$errorMessage = "";
				break;
			case 401:
				$errorName = "Unauthorized";
				$errorMessage = "You do not have permission to view the page you have requested.";
				break;
			case 403:
				$errorName = "Forbidden";
				$errorMessage = "";
				break;
			case 404:
				$errorName = "Not Found";
				$errorMessage = "The page you're looking was not found.";
				break;
			case 408:
				$errorName = "Request Timeout";
				$errorMessage = "";
				break;
			case 418:
				$errorName = "I'm a teapot";
				$errorMessage = "";
				break;
			case 429:
				$errorName = "Too Many Requests";
				$errorMessage = "";
				break;
			case 451:
				$errorName = "Unavailable For Legal Reasons";
				$errorMessage = "";
				break;
			case 500:
				$errorName = "Internal Server Error";
				$errorMessage = "An internal server error has occured.";
				break;
			case 501:
				$errorName = "Not Implemented";
				$errorMessage = "The page you're looking for has not yet been implemented.";
				break;
			case 503:
				$errorName = "Service Unavailable";
				$errorMessage = "";
				break;
			case 508:
				$errorName = "Loop Detected";
				$errorMessage = "The server detected an infinite loop while processing the request.";
				break;
			default:
				$errorName = "Unknown Error";
				$errorMessage = "An unknown error has occured.";
				break;
		}

		$this->setErrorName($errorName);
		$this->setErrorMessage($errorMessage);
	}

	/**
	 * Builds and returns the complete formatted HTML content string of this page
	 *
	 * @return string The generated HTML string
	 */
	public function getHTML(): string {
		$html = <<< HTML
		<!DOCTYPE html>
		<html lang="en">
			<head>
				<title>$this->errorCode $this->errorName</title>
			</head>
			<body>
				<h1>$this->errorCode $this->errorName</h1>
				<p>$this->errorMessage</p>
			</body>
		</html>
		HTML;
		return $html;
	}

	/**
	 * Getter for the error code
	 *
	 * @return int
	 * @see DefaultErrorPage::$errorCode
	 */
	public function getErrorCode(): int {
		return $this->errorCode;
	}

	/**
	 * Setter for the error code
	 *
	 * @param int $errorCode
	 * @see DefaultErrorPage::$errorCode
	 */
	private function setErrorCode(int $errorCode): void {
		// Check if the error code is less than 100
		// If no, throw error: Error code cannot be less than 100
		if($errorCode < 100) {
			throw new ErrorException("The given error code must be a positive number!");
		}

		$this->errorCode = $errorCode;
	}

	/**
	 * Getter for the error name
	 *
	 * @return string
	 * @see DefaultErrorPage::$errorName
	 */
	public function getErrorName(): string {
		return $this->errorName;
	}

	/**
	 * Setter for the error name
	 *
	 * @param string $errorName
	 * @see DefaultErrorPage::$errorName
	 */
	private function setErrorName(string $errorName): void {
		// Check if error name is empty
		// If yes, throw error: Error name should not be empty
		if(empty($errorName)) {
			throw new ErrorException("The given error name is empty!");
		}

		$this->errorName = $errorName;
	}

	/**
	 * Getter for the error message
	 *
	 * @return string
	 * @see DefaultErrorPage::$errorMessage
	 */
	public function getErrorMessage(): string {
		return $this->errorMessage;
	}

	/**
	 * Setter for the error message
	 *
	 * @param string $errorMessage
	 * @see DefaultErrorPage::$errorMessage
	 */
	private function setErrorMessage(string $errorMessage): void {
		$this->errorMessage = $errorMessage;
	}

}

