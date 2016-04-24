<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<?php echo form_open("plan/save/{$year}/{$month}", 'class="pure-form" id="confirm-duty"'); ?>

<legend class="content-subhead">Dienste so übernehmen?</legend>

<div class="wrapper">
	<fieldset><table class="pure-table">
		<thead>
			<th>Start</th>
			<th>Ende</th>
			<th>Fahrzeug</th>
		</thead>
		<tbody>
	<?php 
					function add_date($val, $key, $data) {
						$data[0][$data[2] .' '. $key] = $val;
						unset($data[1][$key]);
					}
					
					$keys = array_keys($duties);
					$j = 0;
					for ($i = 0; $i < count($keys) - 1; $i++) {
						echo '<tr>';
						
						$key		= $keys[$i];
						$start		= $duties[$key][0];
						$vehicle	= $duties[$key][1];
						$key		= $keys[++$i];
						$end		= $duties[$key][0];
						
						$start_wday = date('w', $start);
						$start_wday	= $day_names[$start_wday];
						$start_date	= date($date_format, $start);
						$start_time	= date($time_format, $start);
						
						$end_wday	= date('w', $end);
						$end_wday	= $day_names[$end_wday];
						$end_date	= date($date_format, $end);
						$end_time	= date($time_format, $end);

						$start_time_list = array();
						array_walk($time_list, 'add_date', array(&$start_time_list, $time_list, $start_date));
						
						$end_time_list = array();
						array_walk($time_list, 'add_date', array(&$end_time_list, $time_list, $end_date));
					
						echo '<td>';
						echo '<label for="'. $j .'-start" class="center">'. $start_wday .', '. $start_date .'<br/></label>';
						echo form_dropdown("{$j}-start", $start_time_list, $start_date .' '. $start_time);
						echo '</td>';
						
						echo '<td>';
						echo '<label for="'. $j .'-start" class="center">'. $end_wday .', '. $end_date .'<br/></label>';
						echo form_dropdown("{$j}-end", $end_time_list, $end_date .' '. $end_time);
						echo '</td>';
						
						echo '<td>';
						echo form_dropdown("{$j}-vehicle", $vehicles, $vehicle);
						echo '</td>';
						
						echo '</tr>';
						
						echo '<tr><td colspan=3>';
						echo form_input("{$j}-comment", null, 'placeholder="Kommentar" class="duty-comment"');
						echo '</td></tr>';
						
						$j++;
					} 
	?>
		</tbody>
	</table></fieldset>

	<fieldset class="bottom-wrapper">
	<?php if ($this->ion_auth->is_admin()): ?>
		<label for="user_id">Fahrer: </label>
		<?php echo form_dropdown('user_id', $users, $this->ion_auth->get_user_id()); ?>
	<?php endif ?>
	</fieldset>

	<fieldset class="bottom-wrapper">
	<?php echo form_submit('save', 'Alle speichern', 'class="pure-button primary-button"'); ?>
	</fieldset>
</div>

<?php echo form_close(); ?>
