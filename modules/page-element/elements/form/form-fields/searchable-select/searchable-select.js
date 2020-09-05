
// Hide all <select class="sitebuilder-searchable-select"> tags
var selectBoxes = $('.sitebuilder-searchable-select');
selectBoxes.hide();

// Create container and <span> elements and add them to the DOM
var container = $('<div class="sitebuilder-searchable-select--container" tabindex="0"></div>');
var select = $('<span class="sitebuilder-searchable-select--select"></span>');
var arrow = $('<span class="sitebuilder-searchable-select--arrow"></span>');
container.append(select);
container.append(arrow);
container.insertAfter(selectBoxes);

// Set attributes for each form field
$('.sitebuilder-searchable-select--select').each(function() {
	var container = $(this).parent();
	var selectBox = container.prev();
	
	// Set size of container
	container.width(selectBox.actual('width'));
	container.height(selectBox.actual('height'));
	
	// Center children of the container vertically
	container.children().css('line-height', container.height() + 'px');
	
	// Set default select option
	var selectedOption = selectBox.children('option[selected="selected"]');
	if(selectedOption.length == 0) selectedOption = selectBox.children('option:first');
	$(this).text(selectedOption.text());
});

// Click function
$('.sitebuilder-searchable-select--container').mousedown(function(e) {
	// If there already is a dropdown, close it and return
	if($(this).siblings('.sitebuilder-searchable-select--dropdown').length > 0) {
		$(this).siblings('.sitebuilder-searchable-select--dropdown').remove();
		return;
	}
	
	// Remove all previous dropdown menus
	$('.sitebuilder-searchable-select--dropdown').remove();
	
	// Get select <span>
	var select = $(this).children('.sitebuilder-searchable-select--select');
	
	// Create new dropdown
	var dropdown = $('<div class="sitebuilder-searchable-select--dropdown"></div>');
	
	// Create the searchbox for the dropdown and add it as the first child
	// We use a wrapper to set the margins and width of the <input> properly
	var searchbox = $('<input type="text" class="sitebuilder-searchable-select--searchbox">');
	var searchboxWrapper = $('<div class="sitebuilder-searchable-select--searchbox-wrapper"></div>');
	searchboxWrapper.append(searchbox);
	dropdown.append(searchboxWrapper);
	
	// Create options <ul> and append all <option> tags as <li> children
	var options = $('<ul></ul>');
	var selectBox = $(this).siblings('.sitebuilder-searchable-select');
	selectBox.find('option').each(function() {
		// If the value of the <option> is empty, skip
		if($(this).val().length == 0) return;
		
		var option = $('<li></li>');
		option.text($(this).text());
		
		// Click function for <li> tags
		option.click(function() {
			// Set the '.sitebuilder-searchable-select--select' .text() value
			dropdown.siblings('.sitebuilder-searchable-select--container').children('.sitebuilder-searchable-select--select').text(option.text());
			
			// Set the 'selected="selected"' attribute for the proper <option> tag
			selectBox.children('option[selected="selected"]').removeAttr('selected');
			
			// Get selected <option> by .text() (not .value())
			var selectedOption = selectBox.children('option').filter(function() {
				return $(this).text() === option.text();
			});
			selectedOption.attr('selected', 'selected');
			
			// Remove the dropdown
			dropdown.remove();
		});
		
		options.append(option);
	});
	
	// Set dropdown width to match '.sitebuilder-searchable-select--select' width
	dropdown.width($(this).outerWidth());
	dropdown.append(options);
	dropdown.insertAfter($(this));
	
	// When the dropdown appears, focus the searchbox
	// so the user can start typing immediately
	// Had to do it using a timeout function because of weird threading issues (I think)
	// This is basically a hack and is not 'proper'
	setTimeout(function(){
	    searchbox.focus();
	}, 0);
});

// If the container is focused without clicking it (usually by tabbing to it)
// simulate a click when the eter key is pressed
$('.sitebuilder-searchable-select--container').keydown(function(e) {
	// Get keyCode
	var keyCode = e.keyCode || e.which;
	var keyCodes = { enter: 13 };
	
	if(keyCode == keyCodes.enter) {
		e.preventDefault();
		$(this).trigger('click');
	}
});

// If anywhere except the dropdown or the select (or their children) is clicked, remove the dropdown
$(document).mousedown(function(e) {
	if(!$(e.target).is('.sitebuilder-searchable-select--container')
		&& $(e.target).parent('.sitebuilder-searchable-select--container').length == 0
		&& !$(e.target).is('.sitebuilder-searchable-select--dropdown')
		&& $(e.target).parent('.sitebuilder-searchable-select--dropdown').length == 0) {
		
		$('.sitebuilder-searchable-select--dropdown').remove();
	}
});
