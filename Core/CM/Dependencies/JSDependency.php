<?php

namespace SiteBuilder\Core\CM\Dependencies;

use SiteBuilder\Core\CM\Dependency;

/**
 * The JSDependency class provides a convenient way to define a dependency on a JS resource.
 *
 * @author Alpin Gencer
 * @namespace SiteBuilder\Core\CM\Dependency
 * @see Dependency
 */
class JSDependency extends Dependency {

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

		return '<script ' . $params . 'src="' . $normalizedPath . '"></script>';
	}

}

