<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

<?php echo $this->calendar->generate($year, $month); ?>

<br />
<br />

<?php echo form_open("plan/confirm/{$year}/{$month}", 'class="pure-form"'); ?>
<table class="pure-table pure-table-bordered">
	<thead>
		<th>Tag</th>
		<th>Schicht</th>
<?php foreach ($vehicles as $vehicle): ?>
		<th><?php echo $vehicle ?></th>
<?php endforeach ?>
		<th></th>
	</thead>
	
	<tbody>
<?php
		$add = false;
		foreach ($days as $item) {
			$wday	= (int) $item['wday'];
			$wday_s	= $day_names[$wday];
			$type	= $wday === 0 || $wday === 6 ? 'weekend pure-table-odd' : 'working-day';
			$day_s	= $item['day'];
			$day	= (int) $day_s;
			$shift_count = count($disp_times);
			
			$today = "";
			if ($day === $cur_day) {
				$today =" today";
			}
			
			echo "\n<tr class='{$type}{$today}'>\n";
			echo "<td rowspan={$shift_count} id='day-{$day}'>$wday_s,<br />$day_s.$month.$year</td>\n";
			
			$i = 0;
			foreach ($disp_times as $time) {
				$shift_id = "{$day}-{$i}";
				if ($shift_id == $allow_add) {
					$add = true;
				}
				
				if ($i !== 0) {
					echo "\n<tr class='{$type}{$today}'>\n";
				}
				
				echo "<td>{$time}</td>\n";
				
				$j = 0;
				foreach ($vehicles as $vehicle) {
					echo "<td>\n";
					$this->load->view('shift_slot', array('slot_id' => "{$day}-{$i}-{$j}", 'shift_id' => $shift_id, 'add' => $add));
					echo "</td>\n";
					
					$j++;
				}
				
				echo "<td>".form_submit('verify', 'Eintragen', 'class="pure-button secondary-button"')."</td>\n";
				
				echo "</tr>\n";
				
				$i++;
			}
		}
?>
	</tbody>

</table>
<?php echo form_close(); ?>
