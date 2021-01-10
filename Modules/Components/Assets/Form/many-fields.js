
/* Functions for getting 'data-min-fields' and 'data-max-fields' attributes */
var getMinFields = function(element) {
	var minFields = element.attr('data-min-fields');
	
	if(minFields === undefined) {
		minFields = 0;
	}
	
	return minFields;
};

var getMaxFields = function(element) {
	var maxFields = element.attr('data-max-fields');
	
	if(maxFields === undefined) {
		maxFields = Infinity;
	}
	
	return maxFields;
};

/* Add buttons to add and remove fieldsets */
$('.sitebuilder-form:not(.sitebuilder-form--read-only) .sitebuilder-many-fields').append('<button type="button" class="sitebuilder-fieldset-adder"></button>');
$('.sitebuilder-form:not(.sitebuilder-form--read-only) .sitebuilder-many-fields').append('<button type="button" class="sitebuilder-fieldset-remover"></button>');

/* Adding fieldsets */
$('.sitebuilder-fieldset-adder').click(function() {
	
	// Get minimum and maximum number of fieldsets
	var minFields = getMinFields($(this).parent());
	var maxFields = getMaxFields($(this).parent());
	
	// If there are less than max, add one more
	if($(this).siblings('fieldset:not(.sitebuilder-template-fieldset)').length < maxFields) {
		// Clone and manipulate new fieldset
		var clonedField = $(this).siblings('.sitebuilder-template-fieldset').clone(true);
		clonedField.removeClass('sitebuilder-template-fieldset');
		
		// Set name attribute (number each fieldset), if one is defined
		var parent = $(this).parent();
		clonedField.children().each(function() {
			if($(this).attr('name') !== undefined) {
				var childIndex = parent.find('[name^="' + $(this).attr('name') + '"]').length;
				$(this).attr('name', $(this).attr('name') + '_' + childIndex);
			}
		});
		
		// DOM manipulation
		clonedField.insertBefore($(this));
	}
	
	// Enable and disable 'fieldset-adder' and 'fieldset-remover'
	// based on number of fieldsets, minFields and maxFields
	var numFields = $(this).siblings('fieldset:not(.sitebuilder-template-fieldset)').length;
	
	if(numFields >= maxFields) {
		$(this).prop('disabled', true);
	}
	
	if(numFields >= minFields) {
		$(this).siblings('.sitebuilder-fieldset-remover').prop('disabled', false);
	}
	
});

/* Removing fieldsets */
$('.sitebuilder-fieldset-remover').click(function() {
	
	// Get minimum and maximm number of fieldsets
	var minFields = getMinFields($(this).parent());
	var maxFields = getMaxFields($(this).parent());
	
	// If there are more than min, remove last one
	if($(this).siblings('fieldset:not(.sitebuilder-template-fieldset)').length > minFields) {
		// Get last fieldset
		var removedField = $(this).siblings('fieldset:last-of-type');
		
		// DOM manipulation
		removedField.remove();
	}
	
	// Enable and disable 'fieldset-adder' and 'fieldset-remover'
	// based on number of fieldsets, minFields and maxFields
	var numFields = $(this).siblings('fieldset:not(.sitebuilder-template-fieldset)').length;
	
	if(numFields < maxFields) {
		$(this).siblings('.sitebuilder-fieldset-adder').prop('disabled', false);
	}
	
	if(numFields <= minFields) {
		$(this).prop('disabled', true);
	}
	
});

/* Initially disable or enable adder and remover buttons */
$('.sitebuilder-form:not(.sitebuilder-form--read-only) .sitebuilder-many-fields').each(function() {
	// Get minimum and maximum number of fieldsets
	// and how many there already are
	var minFields = getMinFields($(this));
	var maxFields = getMaxFields($(this));
	var numFields = $(this).children('fieldset:not(.sitebuilder-template-fieldset)').length;
	
	// Get 'fieldset-adder' and 'fieldset-remover' buttons
	var fieldsetAdder = $(this).children('.sitebuilder-fieldset-adder');
	var fieldsetRemover = $(this).children('.sitebuilder-fieldset-remover');
	
	// Disable adder and remover buttons
	// based on numFields, minFields and maxFields
	if(numFields >= maxFields) {
		fieldsetAdder.prop('disabled', true);
	}
	
	if(numFields <= minFields) {
		fieldsetRemover.prop('disabled', true);
	}
});
