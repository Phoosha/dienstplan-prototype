<body>
	
<div id="layout">
	<?php $this->load->view('templates/sidemenu'); ?>
	
	<div id="main">
		<div class="content">
			<?php $this->load->view($_view); ?>
		</div>
		<div class="footer">
			<?php $this->load->view('templates/footer'); ?>
		</div>
	</div>
</div>

<!--[if lte IE 8]>
	<link rel="stylesheet" href="https://yui-s.yahooapis.com/pure/0.6.0/grids-responsive-old-ie-min.css">
<![endif]-->
<!--[if gt IE 8]><!-->
	<link rel="stylesheet" href="https://yui-s.yahooapis.com/pure/0.6.0/grids-responsive-min.css">
<!--<![endif]-->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
<script src="js/ui.js" type="text/javascript"></script>

</body>
</html>
