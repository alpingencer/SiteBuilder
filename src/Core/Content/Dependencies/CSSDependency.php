<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content\Dependencies;

use Eufony\Core\Content\AssetDependency;

final class CSSDependency extends AssetDependency {

	public function __construct(string $source) {
		parent::__construct($source);
	}

	public function html(): string {
		$source = $this->source();
		$attributes = $this->attributes();
		return "<link rel=\"stylesheet\" type=\"text/css\" $attributes href=\"$source\">";
	}

}
