<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content\Dependencies;

use SiteBuilder\Core\Content\AssetDependency;

final class CSSDependency extends AssetDependency {

	public function __construct(string $source) {
		parent::__construct($source);
	}

	public function html(): string {
		$source = AssetDependency::path($this->source());
		$params = $this->paramsAsString();
		return "<link rel=\"stylesheet\" type=\"text/css\" $params href=\"$source\">";
	}
	
}
