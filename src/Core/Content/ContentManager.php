<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Core\FrameworkManager;
use SiteBuilder\Utils\Classes\ClassedCollection;
use SiteBuilder\Utils\Traits\ManagedObject;
use SiteBuilder\Utils\Traits\Runnable;
use SiteBuilder\Utils\Traits\Singleton;

final class ContentManager {
	use ManagedObject;
	use Runnable;
	use Singleton;

	private PageConstructor $page;
	private ClassedCollection $components;
	private ClassedCollection $dependencies;

	public function __construct(array $config) {
		$this->setAndAssertManager(FrameworkManager::class);
		$this->assertSingleton();

		$this->page = new PageConstructor();
		$this->components = new ClassedCollection(Component::class);
		$this->dependencies = new ClassedCollection(AssetDependency::class);
	}

	public function run(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(1);

		$this->page->construct($this->components, $this->dependencies);
	}

	public function output(): void {
		$this->assertCallerIsManager();
		$this->assertCurrentRunStage(2);

		echo $this->page->html();
	}

	public function appendToHead(string $content): void {
		$this->page->head .= $content;
	}

	public function page(): PageConstructor {
		return $this->page;
	}

	public function components(): ClassedCollection {
		return $this->components;
	}

	public function dependencies(): ClassedCollection {
		return $this->dependencies;
	}

}
