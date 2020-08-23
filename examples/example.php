<?php
require '../sb.php';
require 'example-component.php';
require 'example-system.php';

use SiteBuilder\SiteBuilderCore;
use SiteBuilder\SiteBuilderPage;

// Initialize SiteBuilderCore object
$sb = new SiteBuilderCore('/sitebuilder');

// Initialize SiteBuilderPage object and add components
$sb->page = new SiteBuilderPage('sitebuilder/examples/example.php');

$sb->page->head .= '<title>SiteBuilder Example</title>';

$sb->page->addComponent(ExampleComponent::newInstance('Hello World!', 5));

// Add systems
$sb->addSystem(new ExampleSystem($sb, 0));

// Run the core
$sb->run();
