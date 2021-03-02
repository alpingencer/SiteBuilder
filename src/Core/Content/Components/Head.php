<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content\Components;

use Eufony\Core\Content\Component;
use Eufony\Core\Content\ContentManager;
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
		$attributes = (string) $this->attributes();

		// Generate <head>
		$html = "<head" . (empty($attributes) ? "" : " $attributes") . ">";

		// Generate HTML boilerplate
		$html .= '<meta charset="UTF-8">';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		$html .= '<meta http-equiv="X-UA-Compatible" content="ie=edge">';

		// If head doesn't defines a <title> tag, trigger a user warning
		if(!str_contains($this->content, '</title>')) {
			trigger_error("Undefined required <title> element in generated HTML", E_USER_WARNING);
		}

		// Add generated content
		$html .= $this->content;

		// Close <head>
		$html .= '</head>';

		// Return the generated HTML
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
