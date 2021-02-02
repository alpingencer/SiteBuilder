<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Core\Website\PageHierarchy;
use SiteBuilder\Utils\Classes\ClassedCollection;
use SiteBuilder\Utils\Classes\Formatter;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

final class ContentManager {
	public const CONFIG_LANG = 'lang';

	use ManagedObject;
	use Runnable;
	use Singleton;

	private ClassedCollection $components;
	private ClassedCollection $dependencies;
	public string $head;
	public string $body;
	private string $lang;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->components = new ClassedCollection(Component::class);
		$this->dependencies = new ClassedCollection(AssetDependency::class);
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
			$this->body .= $component->content();
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
		usort($added_dependencies, fn($d1, $d2) => get_class($d1) <=> get_class($d2));

		// Add dependencies to page
		foreach($added_dependencies as $dependency) {
			$this->head .= Formatter::doubleSpace($dependency->html());
		}
	}

	public function output(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);

		// Generate HTML5 DOCTYPE
		$content = '<!DOCTYPE html>';

		// Generate <html> tag
		$lang = isset($this->lang) ? " lang=\"$this->lang\"" : "";
		$content .= '<html' . $lang . '>';

		// Generate <head>
		$content .= '<head>';

		// Generate HTML boilerplate
		$content .= '<meta charset="UTF-8">';
		$content .= '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">';
		$content .= '<meta http-equiv="X-UA-Compatible" content="ie=edge">';

		// Check if head defines a <title> tag
		// If no, generate SiteBuilder default title
		if(!str_contains($this->head, '</title>')) {
			$hierarchy = PageHierarchy::instance();
			$title = $hierarchy->currentAttribute('title') . ' - ' . $hierarchy->globalAttribute('title');
			$content .= "<title>$title</title>";
		}

		$content .= $this->head;
		$content .= '</head>';

		// Generate <body>
		$content .= '<body>' . $this->body . '</body>';

		// Close <html>
		$content .= '</html>';

		// Format HTML
		$content = Formatter::html($content);

		// Output the result
		echo $content;
	}

	public function appendToHead(string $content): void {
		$this->head .= $content;
	}

	public function components(): ClassedCollection {
		return $this->components;
	}

	public function dependencies(): ClassedCollection {
		return $this->dependencies;
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
		$this->head = '';
		$this->body = '';
		unset($this->lang);
		$this->components->clear();
		$this->dependencies->clear();
	}

}
