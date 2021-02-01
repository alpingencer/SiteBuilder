<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Core\Website\PageHierarchy;
use SiteBuilder\Utils\Classes\Formatter;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Singleton;
use SplObjectStorage;

final class PageConstructor {
	use ManagedObject;
	use Singleton;

	public string $head;
	public string $body;
	private string $lang;

	public function __construct() {
		$this->setAndAssertManager(ContentManager::class);
		$this->assertSingleton();

		$this->clear();
	}

	public function construct(SplObjectStorage $components, SplObjectStorage $dependencies): void {
		$this->assertCallerIsManager();

		// Components
		// Add components to page
		foreach($components as $component) {
			$this->body .= $component->content();
		}

		// Dependencies
		// Add all dependencies
		$added_dependencies = array();
		foreach($dependencies as $dependency) {
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

	public function html(): string {
		$this->assertCallerIsManager();

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

		// Return result
		return $content;
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
	}
}
