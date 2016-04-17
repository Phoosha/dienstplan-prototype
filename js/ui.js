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


$('#menuLink').on('click', function(e) {
	e.preventDefault();
	
	$('#layout').toggleClass('active');
	$('#menu').toggleClass('active');
	$('#menuLink').toggleClass('active');
});


$('input[type="checkbox"]').on('change', function() {
    $('input[name="' + this.name + '"]').not(this).prop('checked', false);
});


$(function() {
	$.datepicker.setDefaults( $.datepicker.regional[ "de" ] );
	$("#startdate-picker").datepicker({
		onClose: function(dateText, inst) { 
			$(this).attr("disabled", false);
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
		}
	});
});
