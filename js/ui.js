/****************************************************************
 * Datepicker locale
 ****************************************************************/
( function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}( function( datepicker ) {

datepicker.regional.de = {
	closeText: "Schließen",
	prevText: "&#x3C;Zurück",
	nextText: "Vor&#x3E;",
	currentText: "Heute",
	monthNames: [ "Januar","Februar","März","April","Mai","Juni",
	"Juli","August","September","Oktober","November","Dezember" ],
	monthNamesShort: [ "Jan","Feb","Mär","Apr","Mai","Jun",
	"Jul","Aug","Sep","Okt","Nov","Dez" ],
	dayNames: [ "Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag" ],
	dayNamesShort: [ "So","Mo","Di","Mi","Do","Fr","Sa" ],
	dayNamesMin: [ "So","Mo","Di","Mi","Do","Fr","Sa" ],
	weekHeader: "KW",
	dateFormat: "dd.mm.yy",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: "" };
datepicker.setDefaults( datepicker.regional.de );

return datepicker.regional.de;

} ) );


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
 * Setup datepicker
 ****************************************************************/
$(function() {
	$.datepicker.setDefaults( $.datepicker.regional[ "de" ] );
	$("#startdate-picker").datepicker({
		onClose: function(dateText, inst) { 
			$(this).attr("disabled", false);

			var endPicker		= $("#enddate-picker");
			var newStartDate	= $(this).datepicker("getDate");
			var endDate			= $(endPicker).datepicker("getDate");

			if (newStartDate > endDate) {
				$(endPicker).datepicker("setDate", newStartDate);
			}
		},
		beforeShow: function(input, inst) {
			$(this).attr("disabled", true);
		}
	});
	$("#enddate-picker").datepicker({
		onClose: function(dateText, inst) { 
			$(this).attr("disabled", false);
		},
		beforeShow: function(input, inst) {
			$(this).attr("disabled", true);
			$(this).datepicker("option", "minDate", $("#startdate-picker").datepicker("getDate"));
		}
	});
});


/****************************************************************
 * Make shift slots selectable
 ****************************************************************/
 // Hide the checkbox with javascript
//~ $('input[class="shift-slot-select"]').attr("style", "display: none");

// Update checkbox and cell on click
$('td.selectable').on('click', function() {
	var checkbox = $(this).find('input[type="checkbox"]');
	var selected = !checkbox.prop('checked');
	
	if (checkbox.length > 0) {
		checkbox.prop('checked', selected);
		$(this).toggleClass('selected', selected);
		
		// allow only one checkbox to be selected per group (=shift)
		$('input[name="' + $(checkbox).attr('name') + '"]').not($(checkbox)).prop('checked', false);
		$('td[name="' + $(this).attr('name') + '"]').not($(this)).toggleClass('selected', false);
	} else {
		window.alert("Diese Schicht ist gesperrt!");
	}
}).on('mouseenter', function() {
	$(this).addClass('hovered-slot');
}).on('mouseleave', function() {
	$(this).removeClass('hovered-slot');
});

// Do not change selection when clicking links
$('td.selectable a').on('click', function(event) {
	event.stopPropagation();
});


$('td.selectable a').on('mouseenter', function(event) {
	$(this).parentsUntil('tr').addClass('dehover');
}).on('mouseleave', function(event) {
	$(this).parents().removeClass('dehover');
});
