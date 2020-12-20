<?php

namespace SiteBuilder\Core\CM\Dependencies;

use SiteBuilder\Core\CM\Dependency;

/**
 * The CSSDependency class provides a convenient way to define a dependency on a CSS resource.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM\Dependency
 * @see Dependency
 */
class CSSDependency extends Dependency {

	/**
	 * {@inheritdoc}
	 * @see \SiteBuilder\Core\CM\Dependency::getHTML()
	 */
	public function getHTML(): string {
		$cm = $GLOBALS['__SiteBuilder_ContentManager'];
		$normalizedPath = Dependency::getNormalizedPath($cm->getFrameworkDirectory(), $this->getSource());

		if(empty($this->getParams())) {
			$params = '';
		} else {
			$params = $this->getParams() . ' ';
		}

		return '<link rel="stylesheet" type="text/css" ' . $params . 'href="' . $normalizedPath . '">';
	}

}

