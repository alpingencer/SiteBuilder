<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content\Components;

use Eufony\Core\Content\Component;
use Eufony\Core\Content\ContentManager;
use Eufony\Core\Website\PageHierarchy;
use Eufony\Utils\Traits\ManagedObject;
use Eufony\Utils\Traits\Singleton;
use Stringable;

final class Head extends Component implements Stringable {
	use ManagedObject;
	use Singleton;

	public string $content;

	public function __construct() {
		$this->setAndAssertManager(ContentManager::class);
		$this->assertSingleton();
		parent::__construct();
		$this->clear();
	}

	public function content(): string {
		$attributes = $this->attributes();

		// Generate <head>
		$html = "<head $attributes>";

		// Generate HTML boilerplate
		$html .= '<meta charset="UTF-8">';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		$html .= '<meta http-equiv="X-UA-Compatible" content="ie=edge">';

		// Check if head defines a <title> tag
		// If no, generate Eufony default title
		if(!str_contains($this->content, '</title>')) {
			$hierarchy = PageHierarchy::instance();
			$title = $hierarchy->currentAttribute('title') . ' - ' . $hierarchy->globalAttribute('title');
			$html .= "<title>$title</title>";
		}

		$html .= $this->content;
		$html .= '</head>';

		return $html;
	}

	public function append(string $content): void {
		$this->content .= $content;
	}

	public function clear(): void {
		$this->assertCallerIsManager();
		$this->content = '';
		$this->attributes()->clear();
	}

}
