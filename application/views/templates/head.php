<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Dienstplan für den Responder einer Feuerwehr">

	<title><?php echo $title; ?> - FRS Irgendwo</title>
	<base href="<?php echo site_url(); ?>">
	<link rel="shortcut icon" href='favicon.ico'>
	
	<link rel="stylesheet" href="https://yui-s.yahooapis.com/pure/0.6.0/pure-min.css">
	<!--[if lte IE 8]>
		<link rel="stylesheet" href="css/layouts/dienstplan-old-ie.css">
	<![endif]-->
	<!--[if gt IE 8]><!-->
		<link rel="stylesheet" href="css/layouts/dienstplan.css">
	<!--<![endif]-->
	
	<script type="text/javascript">
		function addOnloadHandler(element, handler) {
			if (element.addEventListener)
				element.addEventListener("load", handler, false);
			else if (element.attachEvent)
				element.attachEvent("onload", handler);
			else element.onload = handler;
		}
		
		function downloadJQuery() {
			var element = document.createElement("script");
			element.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js";
			document.body.appendChild(element);
			addOnloadHandler(element, downloadJS);
		}
		
		function downloadJQueryUI() {
			var element = document.createElement("script");
			element.src = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js";
			document.body.appendChild(element);
			addOnloadHandler(element, downloadDatepickerJS);
		}
		
		function downloadJS() {
			var element = document.createElement("script");
			element.src = "js/ui.js"
			document.body.appendChild(element);
		}
		
		function downloadDatepickerJS() {
			var element = document.createElement("script");
			element.src = "js/datepicker.js"
			document.body.appendChild(element);
		}
	</script>
</head>
