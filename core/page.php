<?php

namespace SiteBuilder;

use Exception;
use SplObjectStorage;
use SiteBuilder\PageElement\StaticHTMLElement;

class SiteBuilderPage {
	public $head;
	public $body;
	private $lang;
	private $hierarchyPath;
	private $prettyPrint;
	private $components;

	public static function newInstance(string $hierarchyPath): self {
		return new self($hierarchyPath);
	}

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

	public function __construct(string $hierarchyPath) {
		$this->head = '';
		$this->body = '';
		$this->hierarchyPath = $hierarchyPath;
		$this->prettyPrint = true;
		$this->components = new SplObjectStorage();
	}

	public function setLang(string $lang): self {
		$this->lang = $lang;
		return $this;
	}

	public function getLang(): string {
		return $this->lang;
	}

	public function getHierarchyPath(): string {
		return $this->hierarchyPath;
	}

	public function setPrettyPrint(bool $prettyPrint): self {
		$this->prettyPrint = $prettyPrint;
		return $this;
	}

	public function getPrettyPrint(): bool {
		return $this->prettyPrint;
	}

	public function addComponent(SiteBuilderComponent $component): self {
		$this->components->attach($component);
		return $this;
	}

	public function addAllComponents(SiteBuilderComponent ...$components): self {
		foreach($components as $component) {
			$this->addComponent($component);
		}

		return $this;
	}

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

	public function removeAllComponents(SiteBuilderComponent ...$components): self {
		foreach($components as $component) {
			$this->removeComponent($component);
		}

		return $this;
	}

	public function clearComponents(): self {
		$this->components->removeAll($this->components);

		return $this;
	}

	public function getComponent(string $className) {
		foreach($this->components as $component) {
			if(get_class($component) === $className || is_subclass_of($component, $className)) {
				return $component;
			}
		}

		// No component with given class name found, return null
		return null;
	}

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

	public function getAllComponents(): SplObjectStorage {
		return $this->components;
	}

	public function hasComponent(string $className): bool {
		$component = $this->getComponent($className);

		if(is_null($component)) {
			return false;
		} else {
			return true;
		}
	}

	public function echoHTML(string $html, int $priority = 0): self {
		$this->addComponent(StaticHTMLElement::newInstance($html)->setPriority($priority));
		return $this;
	}

	public function matchesFamily(SiteBuilderFamily $family) {
		return $family->matches($this->components);
	}

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
