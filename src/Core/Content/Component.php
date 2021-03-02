<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content;

use BadMethodCallException;
use Eufony\Core\Content\Components\StaticHTML;
use Eufony\Utils\Classes\Collections\AttributeCollection;
use Stringable;

abstract class Component implements Stringable {
	private AttributeCollection $attributes;

	public function __construct() {
		ContentManager::instance()->components()->add($this);
		$this->attributes = new AttributeCollection();
	}

	public final function __toString(): string {
		return $this->content();
	}

	public abstract function content(): string;

	public final function attributes(): AttributeCollection {
		// Assert that the components is not a StaticHTML component: StaticHTML components cannot define attributes
		if($this instanceof StaticHTML) {
			throw new BadMethodCallException("Failed while returning component attributes: StaticHTML components cannot have attributes");
		}

		return $this->attributes;
	}

}
