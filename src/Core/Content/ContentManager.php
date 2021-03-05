<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content;

use Eufony\Core\Content\Components\Body;
use Eufony\Core\Content\Components\Head;
use Eufony\Core\FrameworkManager;
use Eufony\Utils\Classes\Collections\ClassedCollection;
use Eufony\Utils\Classes\Formatter;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Runnable;
use Eufony\Utils\Traits\Singleton;

final class ContentManager {
	public const CONFIG_LANG = 'eufony.eufony.content.lang';

	use ManagedObject;
	use Runnable;
	use Singleton;

	private ClassedCollection $components;
	private ClassedCollection $dependencies;
	private string $lang;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->components = new ClassedCollection(Component::class);
		$this->dependencies = new ClassedCollection(AssetDependency::class);
		new Head();
		new Body();
		$this->clear();

		if(isset($config[ContentManager::CONFIG_LANG])) {
			$this->lang($config[ContentManager::CONFIG_LANG]);
		}
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		// Components
		// Add components to page
		array_map(
			fn($comp) => $this->body()->append($comp),
			array_filter($this->components->array(), fn($comp) => !($comp instanceof Head || $comp instanceof Body))
		);

		// Dependencies
		// Get unique dependencies as array
		$dependencies = array_unique($this->dependencies->array());

		// Sort dependencies by class
		usort($dependencies, fn($d1, $d2) => $d1::class <=> $d2::class);

		// Format and add dependencies to page
		array_map(fn($dependency) => $this->head()->append(Formatter::doubleSpace($dependency)), $dependencies);
	}

	public function output(): void {
		// Generate HTML5 DOCTYPE
		$content = '<!DOCTYPE html>';

		// Generate <html> tag
		$content .= '<html' . (empty($this->lang) ? "" : " lang=\"$this->lang\"") . '>';

		// Generate head and body
		$content .= $this->head();
		if(strtoupper($_SERVER['REQUEST_METHOD']) !== 'HEAD') $content .= $this->body();

		// Close <html>
		$content .= '</html>';

		// Format and output the result
		echo Formatter::html($content);
	}

	public function components(): ClassedCollection {
		return $this->components;
	}

	public function dependencies(): ClassedCollection {
		return $this->dependencies;
	}

	public function head(): Head {
		/** @var $head Head */
		$head = Head::instance();
		return $head;
	}

	public function body(): Body {
		/** @var $body Body */
		$body = Body::instance();
		return $body;
	}

	public function lang(string $lang = null): string|null|self {
		if($lang === null) {
			return $this->lang ?? null;
		} else {
			$this->lang = $lang;
			return $this;
		}
	}

	public function clear(): void {
		$this->head()->clear();
		$this->body()->clear();
		unset($this->lang);
		$this->components->clear();
		$this->dependencies->clear();
	}

}
