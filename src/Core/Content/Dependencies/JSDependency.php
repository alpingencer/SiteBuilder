<?php
/**************************************************
 *            The Eufony PHP Framework            *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace Eufony\Core\Content\Dependencies;

use Eufony\Core\Content\AssetDependency;

final class JSDependency extends AssetDependency {

	public function __construct(string $source, bool $defer = false) {
		parent::__construct($source);
		if($defer) $this->attributes()->set('defer');
	}

	public function html(): string {
		$source = $this->source();
		$attributes = $this->attributes();
		return "<script $attributes src=\"$source\"></script>";
	}

}
