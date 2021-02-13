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

final class Body extends Component implements Stringable {
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
		return "<body $attributes>" . $this->content . '</body>';
	}

	public function append($content): void {
		$this->assertCallerIsManager();
		$this->content .= $content;
	}

	public function clear(): void {
		$this->assertCallerIsManager();
		$this->content = '';
		$this->attributes()->clear();
	}

}
