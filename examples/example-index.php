<?php
require '../sb.php';
require 'example-component.php';
require 'example-system.php';

use SiteBuilder\SiteBuilderCore;

// Pre-proccessing before SiteBuilder stuff
$pageHierarchy = json_decode(file_get_contents('example-page-hierarchy.json'), true);
$defaultPage = 'home';


// Initialize SiteBuilderCore object
$sb = new SiteBuilderCore($pageHierarchy, 'sitebuilder', 'sitebuilder/examples/pages', $defaultPage);


// Add systems
$sb->addSystem(new ExampleSystem());


// Add components
$sb->getCurrentPage()->setLang('en');
$sb->getCurrentPage()->head .= '<title>SiteBuilder Example Site</title>';


// Run the core
$sb->run();
