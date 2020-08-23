
$('.sitebuilder-accordion-nav .sitebuilder-has-submenu > a').click(function() {
	// Get submenu ul
	var ul = $(this).next();

	if(ul.is(':visible')) {
		// Collapse submenu
		$(this).closest('li').removeClass('sitebuilder-submenu-active');
		ul.slideUp('normal');
	} else {
		// Collapse all other submenus
		ul.parent().siblings().removeClass('sitebuilder-submenu-active');
		ul.parent().siblings().find('.sitebuilder-submenu-active').removeClass('sitebuilder-submenu-active');
		ul.parent().siblings().find('ul').slideUp('normal');

		// Expand submenu
		$(this).closest('li').addClass('sitebuilder-submenu-active');
		ul.slideDown('normal');
	}
});

// Initially slide all submenus up
$('.sitebuilder-accordion-nav .sitebuilder-has-submenu > ul').slideUp(0);

// Add active class to all parents of active nodes
$('.sitebuilder-accordion-nav .sitebuilder-submenu-active').parents('.sitebuilder-has-submenu').addClass('sitebuilder-submenu-active');

// Keep current page's link open at start
$('.sitebuilder-accordion-nav .sitebuilder-submenu-active').children().next().slideDown(0);
