<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Utils\Classes;

use Eufony\Utils\Traits\StaticOnly;

class Formatter {
	use StaticOnly;

	public static function html(string $content, string $tab = "\t"): string {
		/* Code taken and modified from: https://stackoverflow.com/a/61990936 */

		// add marker linefeeds to aid the pretty-tokenizer (adds a linefeed between all tag-end boundaries)
		$content = preg_replace('/(>)(<\/*)/', "$1\n$2", $content);

		// now indent the tags
		$token = strtok($content, "\n");

		$result = ''; // holds formatted version as it is built
		$pad = 0; // initial indent
		$indent = 0;
		$matches = array(); // returns from preg_matches()
		$voidTag = false;

		// scan each line and adjust indent based on opening/closing tags
		while($token !== false && strlen($token) > 0) {
			$token = trim($token);
			// test for the various tag states
			if(preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
				// 1. open and closing tags on same line - no change
				$indent = 0;
			} else {
				if(preg_match('/^<\/\w/', $token, $matches)) {
					// 2. closing tag - outdent now
					$pad--;
					if(isset($indent) && $indent > 0) {
						$indent = 0;
					}
				} else {
					if(preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
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
				}
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

		// end with new line
		$result .= "\n";

		return $result;
	}

	public static function doubleSpace(string $content): string {
		return preg_replace('/ {2,}/', ' ', $content);
	}

}
