<?php echo form_open('auth/login', 'class="pure-form pure-form-stacked" id="login"'); ?>
	<fieldset>
		<h2 class="content-subhead">Anmeldung</h2>
		
		<div id="infoMessage"><?php echo $message; ?></div>
		
		<label for="user">Nutzername</label>
		<?php 
			$user['tabindex'] = 1;
			$user['autofocus'] = null;
			echo form_input($user); ?>
		
		<label for="password">Passwort</label>
		<?php 
			$password['tabindex'] = 2;
			echo form_input($password); ?>
		
		<label for="remember" class="pure-checkbox">
            <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>  Angemeldet bleiben
        </label>
		
		<?php echo form_submit('login', 'Anmelden', 'class="pure-button primary-button" tabindex=3'); ?>
	</fieldset>
<?php echo form_close(); ?>
