<body onload="downloadJQuery()">
	
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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

</body>
</html>
