<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content;

use Eufony\Utils\Collections\AttributeCollection;
use Eufony\Utils\Server\File;
use Stringable;

abstract class AssetDependency implements Stringable {
	private string $source;
	private AttributeCollection $attributes;

	public final static function path(string $source): string {
		return !File::isAbsolutePath($source) && File::exists("/public/assets/$source")
			? "/assets/$source"
			: $source;
	}

	public function __construct(string $source) {
		ContentManager::instance()->dependencies()->add($this);
		$this->source = AssetDependency::path($source);
		$this->attributes = new AttributeCollection();
	}

	public final function __toString(): string {
		return $this->html();
	}

	public abstract function html(): string;

	public final function source(): string {
		return $this->source;
	}

	public final function attributes(): AttributeCollection {
		return $this->attributes;
	}

}
