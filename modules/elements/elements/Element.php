<?php

namespace SiteBuilder\Elements;

use SiteBuilder\Component;

abstract class Element extends Component {

	public function __construct() {}

	public abstract function getDependencies(): array;

	public abstract function getContent(): string;

}
