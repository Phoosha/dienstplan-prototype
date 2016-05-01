/****************************************************************
 * Mobile hidden menu
 ****************************************************************/
$('#menuLink').on('click', function(e) {
	e.preventDefault();
	
	$('#layout').toggleClass('active');
	$('#menu').toggleClass('active');
	$('#menuLink').toggleClass('active');
});


/****************************************************************
 * Make shift slots selectable
 ****************************************************************/

function selectShift(shiftSlot, selected = null) {
	// filter shifts with multiple selections
	shiftSlot = $(shiftSlot).filter(function() {
		return $(shiftSlot).not($(this)).filter('td[name="' + $(this).attr('name') + '"]').length == 0;
	});
	
	var checkbox = $(shiftSlot).find('input[type="checkbox"]');
	if (selected == null) {
		var selected = !checkbox.prop('checked');
	}
	
	checkbox.prop('checked', selected);
	$(shiftSlot).toggleClass('selected', selected);
	
	// allow only one checkbox to be selected per group (=shift)
	$('input[name="' + $(checkbox).attr('name') + '"]').not($(checkbox)).prop('checked', false);
	$('td[name="' + $(shiftSlot).attr('name') + '"]').not($(shiftSlot)).toggleClass('selected', false);
}

// Hide the checkboxes with javascript
$('input[class="shift-slot-select"]').hide();

// Update the state in case user did selections without js loaded
selectShift($('td.selectable').has('input[type="checkbox"]:checked'), true);

// Update checkbox and cell on click and hover
$('td.shift-slot').on('click', function() {
	if ($(this).is('.selectable')) {
		selectShift(this);
	} else {
		window.alert("Diese Schicht ist gesperrt!");
	}
}).filter('td.selectable').on('mouseenter', function() {
	$(this).addClass('hovered-slot');
}).on('mouseleave', function() {
	$(this).removeClass('hovered-slot');
});

// Do not change selection when clicking links
$('td a').on('click', function(event) {
	event.stopPropagation();
}).filter('.selectable').on('mouseenter', function(event) {
	$(this).parentsUntil('tr').addClass('dehover');
}).on('mouseleave', function(event) {
	$(this).parents().removeClass('dehover');
});


/****************************************************************
 * Allow hiding of past days
 ****************************************************************/
 $('tr.hideable').hide();
 $('#hider-show').show().on('click', function() {
	 $('tr.hideable').show();
	 $(this).hide();
	 $('#hider-hide').show();
 });
 $('#hider-hide').on('click', function() {
	 $('tr.hideable').hide();
	 $(this).hide();
	 $('#hider-show').show();
 });
