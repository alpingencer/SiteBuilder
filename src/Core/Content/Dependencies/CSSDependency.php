<?php
/**************************************************
 *          The SiteBuilder PHP Framework         *
 *         Copyright (c) 2021 Alpin Gencer        *
 *      Refer to LICENSE.md for a full notice     *
 **************************************************/

namespace SiteBuilder\Core\Content\Dependencies;

use SiteBuilder\Core\Content\Dependency;

final class CSSDependency extends Dependency {
	public function html(): string {
		$source = Dependency::path($this->source());
		$params = $this->paramsAsString();
		return "<link rel=\"stylesheet\" type=\"text/css\" $params href=\"$source\">";
	}
}
