<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Core\Website\PageHierarchy;
use SiteBuilder\Utils\Bundled\Classes\Formatter;
use SiteBuilder\Utils\Bundled\Traits\ManagedObject;
use SiteBuilder\Utils\Bundled\Traits\Singleton;

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

	public function html(): string {
		// Generate HTML5 DOCTYPE
		$content = '<!DOCTYPE html>';

		// Generate <html> tag
		$lang = isset($this->lang) ? " lang=\"$this->lang\"" : "";
		$content .= '<html' . $lang . '>';

		// Generate <head>
		$content .= '<head>';

		// Check if head defines a <title> tag
		// If no, generate SiteBuilder default title
		if(!str_contains($this->head, '</title>')) {
			// No <title> tag found in page head
			$hierarchy = PageHierarchy::instance();
			$title = $hierarchy->currentAttribute('title') . ' - ' . $hierarchy->globalAttribute('title');
			$content .= "<title>$title</title>";
		}

		$content .= $this->head . '</head>';

		// Generate <body>
		$content .= '<body>' . $this->body . '</body>';

		// Close <html>
		$content .= '</html>';

		// Pretty print
		$content = Formatter::html($content);

		// Return result
		return $content;
	}

	public function clear(): void {
		$this->head = '';
		$this->body = '';
		unset($this->lang);
	}

	public function lang(string $lang = null): string|self {
		if($lang === null) {
			return $this->lang;
		} else {
			$this->lang = $lang;
			return $this;
		}
	}
}
