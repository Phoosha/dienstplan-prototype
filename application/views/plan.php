<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<script type="text/javascript">$(function() { $('input[class="shift-slot-select"]').attr("style", "display: none"); })</script>

<?php echo $this->calendar->generate($year, $month); ?>

<br />
<br />
<div id="infoMessage"><?php echo $message; ?></div>

<?php echo form_open("plan/confirm/{$year}/{$month}", 'class="pure-form"'); ?>
<table class="pure-table pure-table-bordered tight-table" id="plan">
	<thead>
		<th>Tag</th>
		<th>Schicht</th>
<?php foreach ($vehicles as $vehicle): ?>
		<th><?php echo $vehicle ?></th>
<?php endforeach ?>
<?php if ($allow_add !== 'never'): ?>
		<th></th>
<?php endif ?>
	</thead>
	
	<tbody>
<?php
		$add = false;
		$disabled = "disabled";
		foreach ($days as $item) {
			$wday	= (int) $item['wday'];
			$wday_s	= $day_names[$wday];
			$type	= $wday === 0 || $wday === 6 ? 'weekend' : 'working-day';
			$day_s	= $item['day'];
			$day	= (int) $day_s;
			$shift_count = count($disp_times);
			
			$today = "";
			if ($day === $cur_day) {
				$today ="today";
			}
			
			echo "\n<tr class='{$type} {$today}'>\n";
			echo "<td rowspan={$shift_count} id='day-{$day}'>$wday_s,<br />$day_s.$month.$year</td>\n";
			
			$i = 0;
			foreach ($disp_times as $time) {
				$shift_id = "{$day}-{$i}";
				if ($shift_id == $allow_add) {
					$add = true;
					$disabled = "selectable";
				}
				
				if ($i !== 0) {
					echo "\n<tr class='{$type} {$today}'>\n";
				}
				
				echo "<td class=\"shift-name\"><p>{$time}</p></td>\n";
				
				$j = 0;
				foreach ($vehicles as $vehicle) {
					$slot_id	= "{$day}-{$i}-{$j}";
					$empty		= '';
					if (! isset($continuity[$shift_id]) || $continuity[$shift_id] === 0) {
						$empty = "empty-slot";
					} else if ($continuity[$shift_id] < 2) {
						$empty = "alone-slot";
					}
					
					echo "<td class=\"shift-slot {$disabled} {$empty}\" name=shift-".$shift_id.">\n";
					$this->load->view('shift_slot', array('slot_id' => $slot_id, 'shift_id' => $shift_id, 'add' => $add));
					echo "</td>\n";
					
					$j++;
				}
				
				if ($add) {
					echo '<td><button type="submit" name="verify" title="Eintragen" class="pure-button secondary-button fa fa-paper-plane-o" aria-type="hidden" /></td>'."\n";
				} else if ($allow_add !== 'never') {
					echo "<td></td>\n";
				}
				
				echo "</tr>\n";
				
				$i++;
			}
		}
?>
	</tbody>

</table>
<?php echo form_close(); ?>
