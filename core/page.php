<?php

namespace SiteBuilder;

use Exception;
use SplObjectStorage;
use SiteBuilder\PageElement\StaticHTMLElement;

/**
 * The class storing the current page of the framework.
 * Use the $head public field to directly manipulate the head content
 * Add SiteBuilderComponents to this page to indirectly manipulate the body content
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder
 * @see SiteBuilderCore
 * @see SiteBuilderComponent
 */
class SiteBuilderPage {
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
	 * The html lang attribute to set
	 *
	 * @var string
	 */
	private $lang;
	/**
	 * The page's path in the page hierarchy given to the core
	 *
	 * @var string
	 */
	private $hierarchyPath;
	/**
	 * Wether or not to pre-format and beautify the output
	 *
	 * @var bool
	 */
	private $prettyPrint;
	/**
	 * The components added to this page
	 *
	 * @var SplObjectStorage
	 */
	private $components;

	/**
	 * Return an instance of SiteBuilderPage
	 *
	 * @return self The instantiated instance
	 * @see SiteBuilderPage::__construct()
	 */
	public static function newInstance(string $hierarchyPath): self {
		return new self($hierarchyPath);
	}

	/**
	 * Beautify a given HTML string
	 *
	 * @param string $content The content to format
	 * @param string $tab The tab character to be used
	 * @return string The formatted output
	 */
	public static function prettifyHTML(string $content, string $tab = "\t"): string {
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

	/**
	 * Constructor for the page
	 *
	 * @param string $hierarchyPath The page's path in the page hierarchy given to the core
	 */
	public function __construct(string $hierarchyPath) {
		$this->head = '';
		$this->body = '';
		$this->hierarchyPath = $hierarchyPath;
		$this->prettyPrint = true;
		$this->components = new SplObjectStorage();
	}

	/**
	 * Setter for $lang
	 *
	 * @param string $lang
	 * @return self Returns itself to chain other functions
	 */
	public function setLang(string $lang): self {
		$this->lang = $lang;
		return $this;
	}

	/**
	 * Getter for $lang
	 *
	 * @return string
	 */
	public function getLang(): string {
		return $this->lang;
	}

	/**
	 * Getter for $hierarchyPath
	 *
	 * @return string
	 */
	public function getHierarchyPath(): string {
		return $this->hierarchyPath;
	}

	/**
	 * Setter for $prettyPrint
	 *
	 * @param bool $prettyPrint
	 * @return self Returns itself to chain other functions
	 */
	public function setPrettyPrint(bool $prettyPrint): self {
		$this->prettyPrint = $prettyPrint;
		return $this;
	}

	/**
	 * Getter for $prettyPrint
	 *
	 * @return bool
	 */
	public function getPrettyPrint(): bool {
		return $this->prettyPrint;
	}

	/**
	 * Add a component to this page
	 *
	 * @param SiteBuilderComponent $component The component to be added
	 * @return self Returns itself to chain other functions
	 */
	public function addComponent(SiteBuilderComponent $component): self {
		$this->components->attach($component);
		return $this;
	}

	/**
	 * Add all given components to this page
	 *
	 * @param SiteBuilderComponent ...$components The components to be added
	 * @return self Returns itself to chain other functions
	 */
	public function addAllComponents(SiteBuilderComponent ...$components): self {
		foreach($components as $component) {
			$this->addComponent($component);
		}

		return $this;
	}

	/**
	 * Remove a component from this page
	 *
	 * @param SiteBuilderComponent $component The component to be removed
	 * @return self Returns itself to chain other functions
	 */
	public function removeComponent(SiteBuilderComponent $component): self {
		// Check if the component has been added to the page
		if(!$this->components->contains($component)) {
			$e = new Exception();
			trigger_error("The given component was not found!\nStack trace:\n" . $e->getTraceAsString(), E_USER_NOTICE);
		} else {
			$this->components->detach($component);
		}

		return $this;
	}

	/**
	 * Remove all the given components from this page
	 *
	 * @param SiteBuilderComponent ...$components The components to be removed
	 * @return self Returns itself to chain other functions
	 */
	public function removeAllComponents(SiteBuilderComponent ...$components): self {
		foreach($components as $component) {
			$this->removeComponent($component);
		}

		return $this;
	}

	/**
	 * Remove all the components added to this page
	 *
	 * @return self Returns itself to chain other functions
	 */
	public function clearComponents(): self {
		$this->components->removeAll($this->components);

		return $this;
	}

	/**
	 * Get the first component added to this page by its class name
	 *
	 * @param string $className The class name to be searched for
	 * @return SiteBuilderComponent|NULL The component if one is found, null otherwise
	 */
	public function getComponent(string $className) {
		foreach($this->components as $component) {
			if(get_class($component) === $className || is_subclass_of($component, $className)) {
				return $component;
			}
		}

		// No component with given class name found, return null
		return null;
	}

	/**
	 * Get all components added to this page of a certain class
	 *
	 * @param string $className The class name to be searched for
	 * @return SplObjectStorage|NULL An SplObjectStorage containing the components found, or null if none are found
	 */
	public function getComponents(string $className) {
		$ret = new SplObjectStorage();

		foreach($this->components as $component) {
			if(get_class($component) === $className || is_subclass_of($component, $className)) {
				$ret->attach($component);
			}
		}

		if($ret->count() === 0) {
			return null;
		} else {
			return $ret;
		}
	}

	/**
	 * Getter for $components
	 *
	 * @return SplObjectStorage
	 */
	public function getAllComponents(): SplObjectStorage {
		return $this->components;
	}

	/**
	 * Check if the page has at least 1 of the given component class name
	 * This is a shorthand for $page->matchesFamily(SiteBuilderFamily::newInstance()->requireAll($className))
	 *
	 * @param string $className The class name to be checked
	 * @return bool The boolean result
	 * @see SiteBuilderPage::matchesFamily(SiteBuilderFamily $family)
	 */
	public function hasComponent(string $className): bool {
		$component = $this->getComponent($className);

		if(is_null($component)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Convenience function for outputting static HTML into the page.
	 * This is the same as $page->addComponent(StaticHTMLElement::newInstance($html)->setPriority($priority))
	 *
	 * @param string $html The html to be outputted
	 * @param int $priority The priority of the StaticHTMLElement to create
	 * @return self Returns itself for chaining other functions
	 * @see StaticHTMLElement
	 */
	public function echoHTML(string $html, int $priority = 0): self {
		$this->addComponent(StaticHTMLElement::newInstance($html)->setPriority($priority));
		return $this;
	}

	/**
	 * Check if this page matches a given family
	 *
	 * @param SiteBuilderFamily $family The family to be checked against
	 * @return bool The boolean result
	 */
	public function matchesFamily(SiteBuilderFamily $family) {
		return $family->matches($this->components);
	}

	/**
	 * Get the content to be outputted to the browser
	 * To 'prettify' the output, set $page->setPrettyPrint(true)
	 *
	 * @return string The generated HTML
	 */
	public function getHTML(): string {
		$content = '<!DOCTYPE html>';

		if(isset($this->lang)) {
			$content .= '<html lang="' . $this->lang . '">';
		} else {
			$content .= '<html>';
		}

		$content .= '<head>' . $this->head . '</head>';
		$content .= '<body>' . $this->body . '</body>';
		$content .= '</html>';

		if($this->prettyPrint) {
			$content = self::prettifyHTML($content);
		}

		return $content;
	}

}
