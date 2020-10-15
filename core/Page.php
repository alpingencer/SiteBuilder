<?php

namespace SiteBuilder;

use SiteBuilder\Elements\LinkElement;
use SiteBuilder\Elements\StaticHTMLElement;
use ErrorException;
use SplObjectStorage;

class Page {
	public $head;
	public $body;
	private $lang;
	private $pagePath;
	private $prettyPrint;
	private $components;

	public static function newInstance(string $pagePath): self {
		return new self($pagePath);
	}

	public function __construct(string $pagePath) {
		$this->components = new SplObjectStorage();
		$this->clearContent();
		$this->setPagePath($pagePath);
		$this->setPrettyPrint(true);
	}

	public function addHTML(string $html): StaticHTMLElement {
		$component = StaticHTMLElement::newInstance($html);
		$this->addComponent($component);
		return $component;
	}

	public function addLink(string $linkPath): LinkElement {
		$component = LinkElement::newInstance($linkPath);
		$this->addComponent($component);
		return $component;
	}

	public function matchesFamily(Family $family): bool {
		return $family->matches($this->components);
	}

	public function getHTML(): string {
		$content = '<!DOCTYPE html>';

		if(empty($this->lang)) {
			$content .= '<html>';
		} else {
			$content .= '<html lang="' . $this->lang . '">';
		}

		if(empty($this->head)) {
			$content .= '<head><title>SiteBuilder Page</title></head>';
		} else {
			$content .= '<head>' . $this->head . '</head>';
		}

		$content .= '<body>' . $this->body . '</body>';
		$content .= '</html>';

		if($this->prettyPrint) {
			$content = formatHTML($content);
		}

		$content .= "\n";
		return $content;
	}

	public function addComponent(Component $component): void {
		$this->components->attach($component);
	}

	public function addAllComponents(Component ...$components): void {
		foreach($components as $component) {
			$this->addComponent($component);
		}
	}

	public function removeComponent(Component $component): void {
		if($this->components->contains($component)) {
			$this->components->detach($component);
		} else {
			trigger_error("The given component was not found!", E_USER_NOTICE);
		}
	}

	public function removeAllComponents(Component ...$components): void {
		foreach($components as $component) {
			$this->removeComponent($component);
		}
	}

	public function removeAllComponentsByClass(string $class): void {
		if($this->hasComponentsByClass($class)) {
			$this->removeAllComponents($this->getComponentByClass($class));
		} else {
			trigger_error("The given component class '$class' was not found!", E_USER_NOTICE);
		}
	}

	public function clearComponents(): void {
		$this->components->removeAll($this->components);
	}

	public function hasComponentsByClass(string $class): bool {
		try {
			$this->getComponentByClass($class);
			return true;
		} catch(ErrorException $e) {
			return false;
		}
	}

	public function getComponentByClass(string $class): Component {
		foreach($this->components as $component) {
			if(get_class($component) === $class || is_subclass_of($component, $class)) {
				return $component;
			}
		}

		// No component found
		throw new ErrorException("The given component class name '$class' was not found!");
	}

	public function getAllComponentsByClass(string $class): SplObjectStorage {
		$ret = new SplObjectStorage();

		foreach($this->components as $component) {
			if(get_class($component) === $class || is_subclass_of($component, $class)) {
				$ret->attach($component);
			}
		}

		if($ret->count() === 0) {
			// No components found
			throw new ErrorException("The given component class name '$class' was not found!");
		} else {
			return $ret;
		}
	}

	public function getAllComponents(): SplObjectStorage {
		return $this->components;
	}

	public function clearContent(): void {
		$this->clearComponents();
		$this->clearLang();
		$this->head = '';
		$this->body = '';
	}

	public function setLang(string $lang): void {
		$this->lang = $lang;
	}

	public function clearLang(): void {
		$this->setLang('');
	}

	public function getLang(): string {
		return $this->lang;
	}

	private function setPagePath(string $pagePath): void {
		$this->pagePath = normalizePathString($pagePath);
	}

	public function getPagePath(): string {
		return $this->pagePath;
	}

	public function setPrettyPrint(bool $prettyPrint): void {
		$this->prettyPrint = $prettyPrint;
	}

	public function getPrettyPrint(): bool {
		return $this->prettyPrint;
	}

}
