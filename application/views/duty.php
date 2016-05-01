<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
	$edit	= ! empty($id);
	$legend	= $title;
	if ($edit) {
		$button = 'Änderungen speichern';
		$action = 'modify';
	} else {
		$button = 'Dienst eintragen';
		$action = 'add';
	}
?>

<?php echo form_open(current_url(), 'class="pure-form pure-form-stacked" id="duty-form"'); ?>

	<legend class="content-subhead"><?php echo $legend; ?></legend>
	
	<div id="infoMessage"><?php echo $message; ?></div>
	
	<input name="id" value="<?php echo $id; ?>" hidden></input>
	
	<fieldset><div class="pure-g">
		<div class="pure-u-5-8"><div class="input-box">
			<label for="user_id">Fahrer: </label>
			<?php echo form_dropdown('user_id', $user_names, $user_id); ?>
		</div></div>
		
		<div class="pure-u-3-8"><div class="input-box">
			<label for="vehicle">Fahrzeug: </label>
			<?php echo form_dropdown('vehicle', $vehicles, $vehicle); ?>
		</div></div>
		
		<div class="pure-u-1"><div class="input-box">
			<label for="comment">Kommentar: </label>
			<input name="comment" placeholder="Kommentar" value="<?php echo set_value('comment', $comment); ?>"></input>
		</div></div>
		
		<div class="pure-u-1 pure-u-md-1-2">
			<label for="startdate">Dienstanfang: </label>
			<div class="pure-g">
				<div class="pure-u-13-24"><div class="input-box">
					<?php echo form_input("startdate", set_value('startdate', $startdate), 'id="startdate-picker"'); ?>
				</div></div>
				<div class="pure-u-11-24"><div class="input-box">
					<?php echo form_dropdown("starttime", $time_list, $starttime); ?>
				</div></div>
			</div>
		</div>
		
		<div class="pure-u-1 pure-u-md-1-2">
			<label for="enddate" class="pure-u-23-24">Dienstende: </label>
			<div class="pure-g">
				<div class="pure-u-13-24"><div class="input-box">
					<?php echo form_input("enddate", set_value('enddate', $enddate), 'id="enddate-picker"'); ?>
				</div></div>
				<div class="pure-u-11-24"><div class="input-box">
					<?php echo form_dropdown("endtime", $time_list, $endtime); ?>
				</div></div>
			</div>
		</div>
	</div></fieldset>
	
	<?php echo form_submit($action, $button, 'class="pure-button primary-button"'); ?>
	<?php if ($edit) echo form_submit('delete', 'Dienst löschen', 'class="pure-button secondary-button danger-button" onclick="return confirm(\'Dienst wirklich löschen?\')"'); ?>
	
</form>
