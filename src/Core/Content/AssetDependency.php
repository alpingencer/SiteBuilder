<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use SiteBuilder\Utils\Classes\File;
use SiteBuilder\Utils\Traits\HasAttributes;

abstract class AssetDependency {
	use HasAttributes;

	private string $source;

	public final static function removeDuplicates(array &$dependencies): void {
		$added_dependencies = array();
		$added_dependency_sources = array();

		foreach($dependencies as $dependency) {
			if(in_array($dependency->source(), $added_dependency_sources, true)) {
				continue;
			}

			array_push($added_dependency_sources, $dependency->source());
			array_push($added_dependencies, $dependency);
		}

		$dependencies = $added_dependencies;
	}

	public final static function path(string $source): string {
		// Check if source starts with '/'
		// If yes, return unedited string: Absolute path given
		if(File::isAbsolutePath($source)) {
			return $source;
		}

		if(File::exists("/public/assets/$source")) {
			// File in assets folder
			return "/assets/$source";
		} else {
			// File elsewhere
			return $source;
		}
	}

	public function __construct(string $source) {
		ContentManager::instance()->dependencies()->add($this);
		$this->source = $source;
		$this->clearAttributes();
	}

	public abstract function html(string $source, string $attributes): string;

	public final function source(): string {
		return $this->source;
	}

}
