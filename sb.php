<?php

/* SiteBuilder core classes */
require_once 'core/core.php';
require_once 'core/page.php';
require_once 'core/component.php';
require_once 'core/family.php';
require_once 'core/system.php';

/* SiteBuilder modules */
// Database
require_once 'modules/database/database-interface.php';
require_once 'modules/database/database-component.php';
require_once 'modules/database/pdo-database-component.php';

// Internationalization
require_once 'modules/internationalization/internationalization-component.php';
require_once 'modules/internationalization/internationalization-system.php';

// Page Elements
require_once 'modules/page-element/basic/elements/page-element.php';
require_once 'modules/page-element/basic/elements/page-link-element.php';
require_once 'modules/page-element/basic/elements/static-html-element.php';
require_once 'modules/page-element/basic/dependency.php';
require_once 'modules/page-element/basic/page-element-system.php';

// Forms and Lists
require_once 'modules/page-element/forms-and-lists/form-element.php';
require_once 'modules/page-element/forms-and-lists/list-element.php';
require_once 'modules/page-element/forms-and-lists/form-and-list-system.php';

// Javascript Widgets
require_once 'modules/page-element/javascript-widgets/widgets/javascript-widget.php';
require_once 'modules/page-element/javascript-widgets/widgets/accordion-nav-menu/accordion-nav-menu-widget.php';
require_once 'modules/page-element/javascript-widgets/widgets/carousel/carousel-widget.php';
require_once 'modules/page-element/javascript-widgets/widgets/sortable-table/sortable-table-widget.php';

// Authentication
require_once 'modules/authentication/authentication-element.php';
require_once 'modules/authentication/authorization-component.php';
require_once 'modules/authentication/authentication-system.php';
