
var classPrefix = 'sitebuilder-searchable-select';

var selectClass = classPrefix;
var containerClass = classPrefix + '--container';
var textBoxWrapperClass = classPrefix + '--text-box-wrapper';
var textBoxClass = classPrefix + '--text-box';
var arrowClass = classPrefix + '--arrow';
var dropdownClass = classPrefix + '--dropdown';
var searchBoxWrapperClass = classPrefix + '--search-box-wrapper';
var searchBoxClass = classPrefix + '--search-box';
var optionsClass = classPrefix + '--options';

var selectSelector = '.' + selectClass;
var containerSelector = '.' + containerClass;
var textBoxWrapperSelector = '.' + textBoxWrapperClass;
var textBoxSelector = '.' + textBoxClass;
var arrowSelector = '.' + arrowClass;
var dropdownSelector = '.' + dropdownClass;
var searchBoxWrapperSelector = '.' + searchBoxWrapperClass;
var searchBoxSelector = '.' + searchBoxClass;
var optionsSelector = '.' + optionsClass;


$(selectSelector).each(function() {
	// Create DOM nodes
	var select = $(this);
	var container = $('<div class="' + containerClass + '"></div>');
	var textBoxWrapper = $('<span class="' + textBoxWrapperClass +'" tabindex="0"></div>');
	var textBox = $('<span class="' + textBoxClass + '"></span>');
	var arrow = $('<svg class="' + arrowClass + '" viewBox="0 0 448 512"><path d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z" /></svg>');
	var dropdown = $('<div class="' + dropdownClass + '"></div>');
	var searchBoxWrapper = $('<div class="' + searchBoxWrapperClass + '"></div>');
	var searchBox = $('<input class="' + searchBoxClass + '">');
	var options = $('<ol class="' + optionsClass + '"></ol>');
	
	textBoxWrapper.append(textBox, arrow);
	searchBoxWrapper.append(searchBox);
	dropdown.append(searchBoxWrapper, options);
	container.append(textBoxWrapper, dropdown);
	container.insertAfter(select);
	
	// Hide dropdown initially
	dropdown.hide();
	
	// Set size of the text box wrapper
	textBox.width(select.actual('width'));
	textBox.height(select.actual('height'));
	
	// Set textBox text
	var defaultOption = select.children('option[selected="selected"]');
	if(defaultOption.length == 0) defaultOption = select.children('option:first');
	textBox.text(defaultOption.text());
	textBox.attr('data-selected-option-index', defaultOption.index() + 1);
	
	// Add dropdown options
	select.find('option').each(function(index) {
		// Get DOM nodes
		var selectOption = $(this);
		var dropdownOption = $('<li></li>');
		var options = selectOption.parents(selectSelector).next(containerSelector).find(optionsSelector);
		
		// Uncomment the next line to skip <option> tags with empty values.
		// if(selectOption.val().length == 0) return;
		
		// Set dropdown option text and data-option-index attribute
		dropdownOption.text(selectOption.text());
		dropdownOption.attr('data-option-index', index + 1);
		
		// Click function for dropdownOptions
		dropdownOption.click(function() {
			// Get DOM nodes
			var dropdownOption = $(this);
			var container = dropdownOption.parents(containerSelector);
			var textBoxWrapper = container.find(textBoxWrapperSelector);
			var textBox = container.find(textBoxSelector);
			var dropdown = container.find(dropdownSelector);
			var select = container.prev(selectSelector);
			
			// Set the textBox text value and data-selected-option-index attribute
			textBox.text(dropdownOption.text());
			textBox.attr('data-selected-option-index', dropdownOption.attr('data-option-index'));
			
			// Set the selected="selected" attribute for the selectOption
			var selectedOption = select.children('option:nth-child(' + dropdownOption.attr('data-option-index') + ')');
			select.children('option[selected="selected"]').removeAttr('selected');
			selectedOption.attr('selected', 'selected');
			
			// Hide the dropdown and focus the textBoxWrapper
			dropdown.hide();
			textBoxWrapper.focus();
		});
		
		// Add '.hover' class on mouse enter
		dropdownOption.mouseenter(function() {
			var dropdownOption = $(this);
			dropdownOption.siblings().removeClass('hover');
			dropdownOption.addClass('hover');
		});
		
		// Remove '.hover' class on mouse exit
		dropdownOption.mouseout(function() {
			var dropdownOption = $(this);
			dropdownOption.removeClass('hover');
		});
		
		options.append(dropdownOption);
	});
	
	textBoxWrapper.click(function() {
		var textBoxWrapper = $(this);
		var textBox = textBoxWrapper.children(textBoxSelector);
		var dropdown = textBoxWrapper.siblings(dropdownSelector);
		var searchBox = dropdown.find(searchBoxSelector);
		var options = dropdown.find(optionsSelector);
		
		if(dropdown.is(':visible')) {
			// If the dropdown is already visible, hide it and focus the textBoxWrapper
			dropdown.hide();
			textBoxWrapper.focus();
		} else {
			// Hide all previous dropdowns
			$(dropdownSelector).hide();
			
			// If the dropdown is hidden, show it and focus the searchBox
			dropdown.show();
			searchBox.focus();
			
			// De-activate all options
			searchBox.val('');
			options.children('li.hover').removeClass('hover');
			options.children().show();
			
			 // Set focused <li> option on dropdown appear and scroll to it
			// so that it's in the middle
			var selectedOption = options.children('li:nth-child(' + textBox.attr('data-selected-option-index') + ')');
			selectedOption.addClass('hover');
			options.scrollTop(0);
			options.scrollTop(selectedOption.position().top - options.position().top - options.innerHeight() / 2 + selectedOption.outerHeight() / 2);
		}
	});
	
	textBoxWrapper.keydown(function(e) {
		var textBoxWrapper = $(this);
		
		// Get keyCode
		var keyCode = e.keyCode || e.which;
		var keyCodes = { enter: 13, space: 32 };
		
		// If the textBoxWrapper is focused without clicking it (usually by tabbing to it)
		// simulate a click when the enter or space keys are pressed
		if((keyCode == keyCodes.enter || keyCode == keyCodes.space)) {
			e.preventDefault();
			textBoxWrapper.trigger('click');
		}
	});
	
	// Filter options based on searchBox.val()
	searchBox.keyup(function(e) {
		// Get DOM nodes
		var searchBox = $(this);
		var container = searchBox.parents(containerSelector);
		var options = container.find(optionsSelector);
		var select = container.prev(selectSelector);
		
		// Get keyCode
		var keyCode = e.keyCode || e.which;
		var keyCodes = { arrowDown: 40, arrowUp: 38, backspace: 8 };
		
		// Show and hide options based on their text or if the corresponding <option> value is empty
		options.children().each(function() {
			var dropdownOption = $(this);
			
			// Get corresponding select option
			var selectOption = select.children('option:nth-child(' + dropdownOption.attr('data-option-index') + ')');
			
			if(selectOption.val().length != 0 && dropdownOption.text().toLowerCase().match(searchBox.val().toLowerCase())) {
				dropdownOption.show();
			} else {
				dropdownOption.hide();
			}
			
			// If the searchbox is empty, show the options even if the value is empty
			if(searchBox.val().length == 0) {
				dropdownOption.show();
			}
		});
		
		// After typing something, hover on the first visible dropdown option
		if((searchBox.val().length != 0 || keyCode == keyCodes.backspace) && keyCode != keyCodes.arrowDown && keyCode != keyCodes.arrowUp) {
			options.children('li.hover').removeClass('hover');
			options.children('li:visible:first').addClass('hover');
			options.scrollTop(0);
		}
	});
	
	// Scrolling through the dropdown using the arrow keys
	searchBox.keydown(function(e) {
		// Get DOM nodes
		var searchBox = $(this);
		var container = searchBox.parents(containerSelector);
		var textBoxWrapper = container.find(textBoxWrapperSelector);
		var dropdown = container.find(dropdownSelector);
		var options = dropdown.find(optionsSelector);
		
		// Get keyCode
		var keyCode = e.keyCode || e.which;
		var keyCodes = { arrowDown: 40, arrowUp: 38, enter: 13, escape: 27 };
		
		// Prevent default events for enter, escape, up and down arrow keys
		switch(keyCode) {
			case keyCodes.arrowDown:
			case keyCodes.arrowUp:
			case keyCodes.enter:
			case keyCodes.escape:
				e.preventDefault();
				break;
		}
		
		// Snap function
		var snapOptionToEdge = function(dropdownOption) {
			var dropdownOptionPositionRelativeToOptions = options.scrollTop() + dropdownOption.position().top - options.position().top;
			var topOfDropdownOption = dropdownOptionPositionRelativeToOptions;
			var bottomOfDropdownOption = topOfDropdownOption + dropdownOption.outerHeight();
			var topOfOptions = options.scrollTop();
			var bottomOfOptions = topOfOptions + options.innerHeight();
			
			var scrollTopVal = options.scrollTop();
			
			if(topOfDropdownOption < topOfOptions) {
				scrollTopVal += topOfDropdownOption - topOfOptions;
			} else if(bottomOfDropdownOption > bottomOfOptions) {
				scrollTopVal += bottomOfDropdownOption - bottomOfOptions;
			}
			
			options.scrollTop(scrollTopVal);
		}
		
		switch(keyCode) {
			case keyCodes.arrowDown:
				var prevFocus = options.children('li.hover:visible');
				
				// If nothing is in focus, make the first option focused
				// Otherwise get the next non-hidden option
				var newFocus;
				if(prevFocus.length == 0) {
					newFocus = options.children('li:visible:first');
				} else {
					newFocus = prevFocus.nextAll('li:visible:first');
				}
				
				// If at the bottom of the drop down, wrap around to the top
				if(newFocus.length == 0) {
					newFocus = options.children('li:visible:first');
				}
				
				// Add and remove hover classes
				options.children('li.hover').removeClass('hover');
				newFocus.addClass('hover');
				
				// Snap the dropdown option to the edge if it's otherwise off-screen
				snapOptionToEdge(newFocus);
				break;
			case keyCodes.arrowUp:
				var prevFocus = options.children('li.hover:visible');
				
				// If nothing is in focus, make the last option focused
				// Otherwise get the previous non-hidden option
				var newFocus;
				if(prevFocus.length == 0) {
					newFocus = options.children('li:visible:last');
				} else {
					newFocus = prevFocus.prevAll('li:visible:first');
				}
				
				// If at the top of the drop down, wrap around to the bottom
				if(newFocus.length == 0) {
					newFocus = options.children('li:visible:last');
				}
				
				// Add and remove hover classes
				options.children('li.hover').removeClass('hover');
				newFocus.addClass('hover');
				
				// Snap the dropdown option to the edge if it's otherwise off-screen
				snapOptionToEdge(newFocus);
				break;
			case keyCodes.enter:
				// Simulate click event of the <li>
				// so that the .val() of the searchbox gets set
				options.children('li.hover').trigger('click');
				break;
			case keyCodes.escape:
				// Hide the dropdown and focus the textBoxWrapper
				dropdown.hide();
				textBoxWrapper.focus();
				break;
		}
	});
});

$(document).mousedown(function(e) {
	if(!$(e.target).is(containerSelector) && $(e.target).parents(containerSelector).length == 0) {
		$(dropdownSelector).hide();
	}
});
