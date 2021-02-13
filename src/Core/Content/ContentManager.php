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
	public const CONFIG_LANG = 'content.lang';

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
		foreach($this->components as $component) {
			if($component instanceof Head || $component instanceof Body) {
				continue;
			}

			$this->body()->append($component);
		}

		// Dependencies
		// Add all dependencies
		$added_dependencies = array();
		foreach($this->dependencies as $dependency) {
			array_push($added_dependencies, $dependency);
		}

		// Get rid of duplicate dependencies
		AssetDependency::removeDuplicates($added_dependencies);

		// Sort dependencies by class
		usort($added_dependencies, fn($d1, $d2) => $d1::class <=> $d2::class);

		// Add dependencies to page
		foreach($added_dependencies as $dependency) {
			$dependency_html = Formatter::doubleSpace($dependency);
			$this->head()->append($dependency_html);
		}
	}

	public function output(): void {
		// Generate HTML5 DOCTYPE
		$content = '<!DOCTYPE html>';

		// Generate <html> tag
		$lang = isset($this->lang) ? " lang=\"$this->lang\"" : "";
		$content .= '<html' . $lang . '>';

		// Generate head and body
		$content .= $this->head();
		$content .= $this->body();

		// Close <html>
		$content .= '</html>';

		// Format HTML
		$content = Formatter::html($content);

		// Output the result
		echo $content;
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

	public function lang(string $lang = null): string|self {
		if($lang === null) {
			return $this->lang;
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
