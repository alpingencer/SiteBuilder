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

abstract class Component {
	private AttributeCollection $attributes;

	public function __construct() {
		ContentManager::instance()->components()->add($this);
		$this->attributes = new AttributeCollection();
	}

	public abstract function content(): string;

	public final function attributes(): AttributeCollection {
		assert(
			!($this instanceof StaticHTML),
			new BadMethodCallException("Failed while returning component attributes: StaticHTML components cannot have attributes")
		);

		return $this->attributes;
	}

}
