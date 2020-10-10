<?php

namespace SiteBuilder;

use ErrorException;

function normalizePathString(string $path): string {
	$path = str_replace(array('/', '\\'), '/', $path);

	$parts = array_filter(explode('/', $path), 'strlen');
	$absolutes = array();

	foreach($parts as $part) {
		if('.' == $part) continue;
		if('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}

	return implode('/', $absolutes);
}

function normalizeDirectoryString(string $path): string {
	if(substr($path, 0, 1) !== '/') {
		$path = '/' . $path;
	}

	if(substr($path, -1, 1) !== '/') {
		$path = $path . '/';
	}

	return $path;
}

function validatePageHierarchy(array $pageHierarchy, string $currentPath = ''): bool {
	if(isset($pageHierarchy['children'])) {
		// Site (page group)
		$requiredAttributes = array(
				'title'
		);

		// Check if attributes are set
		foreach($requiredAttributes as $requiredAttribute) {
			if(!isset($pageHierarchy[$requiredAttribute])) {
				throw new ErrorException("Required attribute '$requiredAttribute' not set for page '$currentPath' in the given hierarchy!");
				return false;
			}
		}

		// Validate children
		foreach($pageHierarchy['children'] as $childName => $child) {
			validatePageHierarchy($child, $currentPath . '/' . $childName);
		}
	} else {
		// Page
		$requiredAttributes = array(
				'title'
		);

		// Check if attributes are set
		foreach($requiredAttributes as $requiredAttribute) {
			if(!isset($pageHierarchy[$requiredAttribute])) {
				throw new ErrorException("Required attribute '$requiredAttribute' not set for page '$currentPath' in the given hierarchy!");
				return false;
			}
		}
	}

	return true;
}

function cascadePageAttributesDownInHierarchy(array &$pages): void {
	if(!isset($pages['children'])) return;

	foreach(array_keys($pages) as $key) {
		if($key === 'children') continue;

		foreach($pages['children'] as &$child) {
			if(!isset($child[$key])) {
				$child[$key] = $pages[$key];
			}

			cascadePageAttributesDownInHierarchy($child);
		}
	}
}

function getDefaultErrorPage(int $errorCode): array {
	switch($errorCode) {
		case 400:
			$errorName = 'Bad Request';
			$errorMessage = "";
			break;
		case 401:
			$errorName = 'Unauthorized';
			$errorMessage = "You do not have permission to view the page you have requested.";
			break;
		case 403:
			$errorName = 'Forbidden';
			$errorMessage = "";
			break;
		case 404:
			$errorName = 'Not Found';
			$errorMessage = "The page you're looking was not found.";
			break;
		case 408:
			$errorName = 'Request Timeout';
			$errorMessage = "";
			break;
		case 418:
			$errorName = "I'm a teapot";
			$errorMessage = "";
			break;
		case 429:
			$errorName = 'Too Many Requests';
			$errorMessage = "";
			break;
		case 451:
			$errorName = 'Unavailable For Legal Reasons';
			$errorMessage = "";
			break;
		case 500:
			$errorName = 'Internal Server Error';
			$errorMessage = "An internal server error has occured.";
			break;
		case 501:
			$errorName = 'Not Implemented';
			$errorMessage = "The page you're looking for has not yet been implemented.";
			break;
		case 503:
			$errorName = 'Service Unavailable';
			$errorMessage = "";
			break;
		case 508:
			$errorName = 'Loop Detected';
			$errorMessage = "The server detected an infinite loop while processing the request.";
			break;
		default:
			$errorName = 'Unknown Error';
			$errorMessage = 'An unknown error has occured.';
			break;
	}

	return array(
			"errorName" => $errorName,
			"errorMessage" => $errorMessage
	);
}

function getRequestURIWithoutGETArguments(): string {
	return explode('?', $_SERVER['REQUEST_URI'], 2)[0];
}

function formatHTML(string $content, string $tab = "\t"): string {
	/* Code taken and modified from: https://stackoverflow.com/a/61990936 */

	// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	$content = preg_replace('/(>)(<\/*)/', "$1\n$2", $content);

	// now indent the tags
	$token = strtok($content, "\n");

	$result = ''; // holds formatted version as it is built
	$pad = 0; // initial indent
	$matches = array(); // returns from preg_matches()
	$voidTag = false;

	// scan each line and adjust indent based on opening/closing tags
	while($token !== false && strlen($token) > 0) {
		$token = trim($token);
		// test for the various tag states
		if(preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
			// 1. open and closing tags on same line - no change
			$indent = 0;
		} elseif(preg_match('/^<\/\w/', $token, $matches)) {
			// 2. closing tag - outdent now
			$pad--;
			if($indent > 0) $indent = 0;
		} elseif(preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
			// 3. opening tag - don't pad this one, only subsequent tags (only if it isn't a void tag)
			foreach($matches as $m) {
				if(preg_match('/^<(area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)/im', $m)) {
					// Void elements according to http://www.htmlandcsswebdesign.com/articles/voidel.php
					$voidTag = true;
					break;
				}
			}
			$indent = 1;
		} else {
			// 4. no indentation needed
			$indent = 0;
		}

		// pad the line with the required number of leading spaces
		$line = str_pad($token, strlen($token) + $pad, $tab, STR_PAD_LEFT);

		// add to the cumulative result, with linefeed
		$result .= $line . "\n";

		// get the next token
		$token = strtok("\n");
		// update the pad size for subsequent lines
		$pad += $indent;

		if($voidTag) {
			$voidTag = false;
			$pad--;
		}
	}

	// remove all whitespace between empty tags
	$result = preg_replace('/(<)(\S*)(.*>)[\n\s]*(<\/\g2>)/', "$1$2$3$4", $result);

	// strip whitespace from beginning and end
	$result = rtrim($result);

	return $result;
}
