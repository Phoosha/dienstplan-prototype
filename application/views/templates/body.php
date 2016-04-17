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

<script src="js/ui.js" type="text/javascript"></script>

</body>
</html>
