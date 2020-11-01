<?php

namespace SiteBuilder\Core\CM;

/**
 * A PageConstructor is used for easily constructing an HTML document.
 * To use this class, set the content using the head and body public fields, optionally set a
 * language and a 'pretty print' option, and call getHTML().
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM
 * @see PageConstructor::getHTML()
 */
class PageConstructor {
	/**
	 * The string containing the head content of the page
	 *
	 * @var string
	 */
	public $head;
	/**
	 * The string containing the body content of the page
	 *
	 * @var string
	 */
	public $body;
	/**
	 * The language attribute of the HTML
	 *
	 * @var string
	 */
	private $lang;
	/**
	 * Wether to format the HTML when generating it
	 *
	 * @var bool
	 */
	private $prettyPrint;

	/**
	 * Returns an instance of PageConstructor
	 *
	 * @param string $lang The language attribute of the HTML
	 * @param bool $prettyPrint Wether to format the HTML when generating it
	 * @return PageConstructor The initialized instance
	 */
	public static function init(string $lang = '', bool $prettyPrint = true): PageConstructor {
		return new self($lang, $prettyPrint);
	}

	/**
	 * Formats a given HTML string
	 *
	 * @param string $content The content to process
	 * @param string $tab The character to be used for indentation
	 * @return string The resulting HTML string
	 */
	public static function formatHTML(string $content, string $tab = "\t"): string {
		/* Code taken and modified from: https://stackoverflow.com/a/61990936 */

		// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end
		// boundaries)
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
				$pad-- ;
				if($indent > 0) $indent = 0;
			} elseif(preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
				// 3. opening tag - don't pad this one, only subsequent tags (only if it isn't a
				// void tag)
				foreach($matches as $m) {
					if(preg_match('/^<(area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)/im', $m)) {
						// Void elements according to
						// http://www.htmlandcsswebdesign.com/articles/voidel.php
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
				$pad-- ;
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

	/**
	 * Constructor for the PageConstructor.
	 * To get an instance of this class, use PageConstructor::init()
	 *
	 * @param string $lang The language attribute of the HTML
	 * @param bool $prettyPrint Wether to format the HTML when generating it
	 * @see PageConstructor::init()
	 */
	private function __construct(string $lang, bool $prettyPrint) {
		$this->clearContent();
		$this->setLang($lang);
		$this->setPrettyPrint($prettyPrint);
	}

	/**
	 * Generates an HTML string based on the defined head and body content, the HTML language and
	 * the 'pretty print' field
	 *
	 * @return string The generated HTML string
	 */
	public function getHTML(): string {
		// Generate HTML5 DOCTYPE
		$content = '<!DOCTYPE html>';

		// Generate <html> tag
		if(empty($this->lang)) {
			$content .= '<html>';
		} else {
			$content .= '<html lang="' . $this->lang . '">';
		}

		// Generate <head>
		$content .= '<head>';

		// Check if head defines a <title> tag
		// If no, generate SiteBuilder default title
		if(strpos($this->head, '<title>') === false) {
			// No <title> tag found in page head
			$content .= '<title>SiteBuilder Webpage</title>';
		}

		$content .= $this->head . '</head>';

		// Generate <body>
		$content .= '<body>' . $this->body . '</body>';

		// Close <html>
		$content .= '</html>';

		// Pretty print
		if($this->prettyPrint) {
			$content = PageConstructor::formatHTML($content);
		}

		return $content;
	}

	/**
	 * Clears all content associated with this page constructor
	 *
	 * @return self
	 */
	public function clearContent(): self {
		$this->head = '';
		$this->body = '';
		$this->clearLang();
		return $this;
	}

	/**
	 * Getter for the HTML language
	 *
	 * @return string
	 * @see PageConstructor::$lang
	 */
	public function getLang(): string {
		return $this->lang;
	}

	/**
	 * Setter for the HTML language
	 *
	 * @param string $lang
	 * @see PageConstructor::$lang
	 */
	private function setLang(string $lang): void {
		$this->lang = $lang;
	}

	/**
	 * Clears the HTML language
	 *
	 * @see PageConstructor::$lang
	 */
	private function clearLang(): void {
		$this->setLang('');
	}

	/**
	 * Getter for the pretty print parameter
	 *
	 * @return bool
	 * @see PageConstructor::$prettyPrint
	 */
	public function isPrettyPrint(): bool {
		return $this->prettyPrint;
	}

	/**
	 * Setter for the pretty print parameter
	 *
	 * @param bool $prettyPrint $see PageConstructor::$prettyPrint
	 */
	private function setPrettyPrint(bool $prettyPrint): void {
		$this->prettyPrint = $prettyPrint;
	}

}

