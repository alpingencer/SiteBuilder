<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content;

use ErrorException;

abstract class Component {
	private array $attributes;

	public function __construct() {
		ContentManager::instance()->addComponent($this);
		$this->clearAttributes();
	}

	public abstract function content(): string;

	public final function attribute(string $attribute_name, string $value = null): string|self {
		if($value === null) {
			if(!isset($this->attributes[$attribute_name])) {
				throw new ErrorException("Undefined attribute '$attribute_name'!");
			}

			return $this->attributes[$attribute_name];
		} else {
			$this->attributes[$attribute_name] = $value;
			return $this;
		}
	}

	public final function attributes(array $attributes = null): array|self {
		if($attributes === null) {
			return $this->attributes;
		} else {
			$this->attributes = $attributes;
			return $this;
		}
	}

	public final function clearAttributes(): self {
		$this->attributes = array();
		return $this;
	}

	public final function attributesAsString(): string {
		return implode(' ', array_map(fn(string $attribute_name, string $attribute) => "$attribute_name=\"$attribute\"", $this->attributes));
	}
}
