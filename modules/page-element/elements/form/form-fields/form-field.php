<?php

namespace SiteBuilder\PageElement;

abstract class FormField {

	public function __construct() {}

	public abstract function getDependencies(): array;

	public abstract function getContent(): string;

}
