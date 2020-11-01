
$('.sitebuilder-accordion .sitebuilder-accordion--has-submenu > a').click(function() {
	// Get submenu ul
	var ul = $(this).next();

	if(ul.is(':visible')) {
		// Collapse submenu
		$(this).closest('li').removeClass('sitebuilder-accordion--active-submenu');
		ul.slideUp('normal');
	} else {
		// Collapse all other submenus
		ul.parent().siblings().removeClass('sitebuilder-accordion--active-submenu');
		ul.parent().siblings().find('.sitebuilder-accordion--active-submenu').removeClass('sitebuilder-accordion--active-submenu');
		ul.parent().siblings().find('ul').slideUp('normal');

		// Expand submenu
		$(this).closest('li').addClass('sitebuilder-accordion--active-submenu');
		ul.slideDown('normal');
	}
});

// Initially slide all submenus up
$('.sitebuilder-accordion .sitebuilder-accordion--has-submenu > ul').slideUp(0);

// Add active class to all parents of active nodes
$('.sitebuilder-accordion .sitebuilder-accordion--active-submenu').parents('.sitebuilder-accordion--has-submenu').addClass('sitebuilder-accordion--active-submenu');

// Keep current page's link open at start
$('.sitebuilder-accordion .sitebuilder-accordion--active-submenu').children().next().slideDown(0);
