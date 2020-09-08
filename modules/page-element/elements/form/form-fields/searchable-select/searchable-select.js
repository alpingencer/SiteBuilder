
// Hide all <select class="sitebuilder-searchable-select"> tags
var selectBoxes = $('.sitebuilder-searchable-select');
selectBoxes.hide();

// Create container and <span> elements and add them to the DOM
var container = $('<div class="sitebuilder-searchable-select--container" tabindex="0"></div>');
var select = $('<span class="sitebuilder-searchable-select--select"></span>');
var arrow = $('<svg class="sitebuilder-searchable-select--arrow" viewBox="0 0 448 512"><path d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z"/></svg>');
container.append(select);
container.append(arrow);
container.insertAfter(selectBoxes);

// Set attributes for each form field
$('.sitebuilder-searchable-select--select').each(function() {
	var container = $(this).parent();
	var arrow = $(this).siblings('.sitebuilder-searchable-select--arrow');
	var selectBox = container.prev();
	
	// Set size of container
	container.width(selectBox.actual('width') + arrow.actual('width'));
	container.height(selectBox.actual('height'));
	
	// Center children of the container vertically
	$(this).css('line-height', container.actual('height') + 'px');
	arrow.css('margin-top', (container.actual('height') - arrow.actual('height')) / 2 + 'px');
	
	// Set default select option
	var selectedOption = selectBox.children('option[selected="selected"]');
	if(selectedOption.length == 0) selectedOption = selectBox.children('option:first');
	$(this).text(selectedOption.text());
	$(this).attr('data-selected-option-index', selectedOption.index() + 1);
});

// Click function
$('.sitebuilder-searchable-select--container').click(function(e) {
	// If there already is a dropdown, close it and return
	if($(this).siblings('.sitebuilder-searchable-select--dropdown').length > 0) {
		$(this).siblings('.sitebuilder-searchable-select--dropdown').remove();
		return;
	}
	
	// Remove all previous dropdown menus
	$('.sitebuilder-searchable-select--dropdown').remove();
	
	// Get the container
	var container = $(this);
	
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
	selectBox.find('option').each(function(index) {
		// Uncomment the next line to skip <option> tags with empty values.
		// if($(this).val().length == 0) return;
		
		var option = $('<li data-option-index="' + (index + 1) + '"></li>');
		option.text($(this).text());
		
		// Click function for <li> tags
		option.click(function() {
			// Set the '.sitebuilder-searchable-select--select' .text() value and 'data-selected-option-index' attribute
			select.text(option.text());
			select.attr('data-selected-option-index', option.index() + 1);
			
			
			// Set the 'selected="selected"' attribute for the proper <option> tag
			selectBox.children('option[selected="selected"]').removeAttr('selected');
			
			// Get selected <option> using 'data-option-index' attribute
			var selectedOption = selectBox.children('option:nth-child(' + option.attr('data-option-index') + ')');
			selectedOption.attr('selected', 'selected');
			
			// Remove the dropdown
			dropdown.remove();
			
			// Focus the container
			container.focus();
		});
		
		// Add '.hover' class on mouse enter
		option.mouseenter(function(e) {
			option.siblings().removeClass('hover');
			option.addClass('hover');
		});
		
		// Remove '.hover' class on mouse exit
		option.mouseout(function(e) {
			option.removeClass('hover');
		});
		
		options.append(option);
	});
	
	// Set dropdown width to match '.sitebuilder-searchable-select--select' width
	dropdown.width($(this).outerWidth() - parseInt($(this).css('border-left-width')) - parseInt($(this).css('border-right-width')));
	
	// Set dropdown margin-top negative to push up to bottom edge of the container
	dropdown.css('margin-top', '-' + $(this).css('margin-bottom'));
	
	// DOM manipulation
	dropdown.append(options);
	dropdown.insertAfter($(this));
	
	// When the dropdown appears, focus the searchbox
	// so the user can start typing immediately
	// Had to do it using a timeout function because of weird threading issues (I think)
	// This is basically a hack and is not 'proper'
	setTimeout(function(){
	    searchbox.focus();
	}, 0);
	
	// Set focused <li> option on dropdown appear and scroll to it
	// so that it's in the middle
	var selectedOption = options.children('li:nth-child(' + select.attr('data-selected-option-index') + ')');
	if(selectedOption.length > 0) {
		selectedOption.addClass('hover');
		options.scrollTop(selectedOption.position().top - options.position().top - options.innerHeight() / 2 + selectedOption.outerHeight() / 2);
	}
	
	// Filter options based on searchbox.val()
	searchbox.keyup(function(e) {
		// Get keyCode
		var keyCode = e.keyCode || e.which;
		var keyCodes = { backspace: 8 };
		
		// Show and hide options based on their text or if the corresponding <option> value is empty
		options.children().each(function() {
			// Get corresponding <option> tag
			var selectBoxOption = selectBox.children('option:nth-child(' + $(this).attr('data-option-index') + ')');
			
			if($(this).text().toLowerCase().match(searchbox.val().toLowerCase()) && selectBoxOption.val().length != 0) {
				$(this).show();
			} else {
				$(this).hide();
			}
			
			// If the searchbox is empty, show the options even if the value is empty
			if(searchbox.val().length == 0) {
				$(this).show();
			}
		});
		
		// If text is deleted, de-activate all <li> tags
		// This fixes a bug in a specific scenario related to deleting
		if(keyCode == keyCodes.backspace) {
			options.children('li.hover').removeClass('hover');
		}
	});
	
	// Scrolling through the dropdown using the arrow keys
	searchbox.keydown(function(e) {
		// Get keyCode
		var keyCode = e.keyCode || e.which;
		var keyCodes = { arrowDown: 40, arrowUp: 38, enter: 13, escape: 27 };

		// Prevent default events for enter, escape, up and down arrow keys
		if(keyCode == keyCodes.arrowDown || keyCode == keyCodes.arrowUp || keyCode == keyCodes.enter || keyCode == keyCodes.escape) {
			e.preventDefault();
		}
		
		// Snap functions
		var scrollAnimationSpeedMs = 80;
		
		var snapOptionToTop = function(option) {
			var optionPositionRelativeToOptions = options.scrollTop() + option.position().top - options.position().top;
			var topOfOption = optionPositionRelativeToOptions;
			var topOfOptions = options.scrollTop();

			if(topOfOption < topOfOptions) {
				var scrollTopVal = options.scrollTop() + topOfOption - topOfOptions;
				options.animate({ scrollTop: scrollTopVal }, scrollAnimationSpeedMs);
			}
		};
		
		var snapOptionToBottom = function(option) {
			var optionPositionRelativeToOptions = options.scrollTop() + option.position().top - options.position().top;
			var bottomOfOption = optionPositionRelativeToOptions + option.outerHeight();
			var bottomOfOptions = options.scrollTop() + options.innerHeight();

			if(bottomOfOption > bottomOfOptions) {
				var scrollTopVal = options.scrollTop() + bottomOfOption - bottomOfOptions;
				options.animate({ scrollTop: scrollTopVal }, scrollAnimationSpeedMs);
			}
		};

		switch(keyCode) {
			case keyCodes.arrowDown:
				var prevFocus = options.children('li.hover:not(:hidden)');

				// If nothing is in focus, make the first option focused
				// Otherwise get the next non-hidden option
				var newFocus;
				if(prevFocus.length == 0) {
					newFocus = options.children('li:not(:hidden):first');
					options.scrollTop(0);
				} else {
					newFocus = prevFocus.nextAll('li:not(:hidden):first');
				}
				
				// If at the bottom of the drop down, wrap around to the top
				var snapDir;
				if(newFocus.length == 0) {
					newFocus = options.children('li:not(:hidden):first');
					snapDir = 'top';
				} else {
					snapDir = 'bottom';
				}
				
				// Add or remove hover classes
				options.children('li.hover').removeClass('hover');
				newFocus.addClass('hover');
				
				// Scroll to the new option so that it's at the correct position in the dropdown
				switch(snapDir) {
					case 'bottom':
						snapOptionToBottom(newFocus);
						break;
					case 'top':
						snapOptionToTop(newFocus);
						break;
				}
				break;
			case keyCodes.arrowUp:
				var prevFocus = options.children('li.hover:not(:hidden)');

				// If nothing is in focus, make the last option focused
				// Otherwise get the previous non-hidden option
				var newFocus;
				if(prevFocus.length == 0) {
					newFocus = options.children('li:not(:hidden):last');
					options.scrollTop(options.children('li:not(:hidden)').length * newFocus.outerHeight());
				} else {
					newFocus = prevFocus.prevAll('li:not(:hidden):first');
				}
				
				// If at the top of the drop down, wrap around to the bottom
				var snapDir;
				if(newFocus.length == 0) {
					newFocus = options.children('li:not(:hidden):last');
					snapDir = 'bottom';
				} else {
					snapDir = 'top';
				}
				
				options.children('li.hover').removeClass('hover');
				newFocus.addClass('hover');

				// Scroll to the new option so that it's at the correct position in the dropdown
				switch(snapDir) {
					case 'top':
						snapOptionToTop(newFocus);
						break;
					case 'bottom':
						snapOptionToBottom(newFocus);
						break;
				}
				break;
			case keyCodes.enter:
				// Simulate click event of the <li>
				// so that the .val() of the searchbox gets set
				options.children('li.hover').trigger('click');
				break;
			case keyCodes.escape:
				$(this).parents('.sitebuilder-searchable-select--dropdown').prev().focus();
				$(this).parents('.sitebuilder-searchable-select--dropdown').remove();
				break;
		}
	});
});

$('.sitebuilder-searchable-select--container').keydown(function(e) {
	// Get keyCode
	var keyCode = e.keyCode || e.which;
	var keyCodes = { enter: 13, space: 32 };
	
	// If the container is focused without clicking it (usually by tabbing to it)
	// simulate a click when the enter or space keys are pressed
	if(keyCode == keyCodes.enter || keyCode == keyCodes.space ) {
		e.preventDefault();
		$(this).trigger('click');
	}
});

// If anywhere except the dropdown or the select (or their children) is clicked, remove the dropdown
$(document).mousedown(function(e) {
	if(!$(e.target).is('.sitebuilder-searchable-select--container')
		&& $(e.target).parents('.sitebuilder-searchable-select--container').length == 0
		&& !$(e.target).is('.sitebuilder-searchable-select--dropdown')
		&& $(e.target).parents('.sitebuilder-searchable-select--dropdown').length == 0) {
		
		$('.sitebuilder-searchable-select--dropdown').remove();
	}
});
