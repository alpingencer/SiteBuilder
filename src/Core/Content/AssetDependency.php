<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use ErrorException;
use SiteBuilder\Utils\Classes\File;

abstract class AssetDependency {
	private string $source;
	private array $params;

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
		$this->clearParams();
	}

	public abstract function html(): string;

	public final function source(): string {
		return $this->source;
	}

	public final function param(string $param_name, string $value = null): string|self {
		if($value === null) {
			if(!isset($this->params[$param_name])) {
				throw new ErrorException("Undefined parameter '$param_name'!");
			}

			return $this->params[$param_name];
		} else {
			$this->params[$param_name] = $value;
			return $this;
		}
	}

	public final function params(array $params = null): array|self {
		if($params === null) {
			return $this->params;
		} else {
			$this->params = $params;
			return $this;
		}
	}

	public final function clearParams(): self {
		$this->params = array();
		return $this;
	}

	public final function paramsAsString(): string {
		return implode(' ', array_map(fn(string $param_name, string $param) => "$param_name=\"$param\"", array_keys($this->params), array_values($this->params)));
	}

}
