<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<?php echo $this->calendar->generate($year, $month); ?>

<br />
<br />
<div id="infoMessage"><?php echo $message; ?></div>

<?php echo form_open("plan/confirm/{$year}/{$month}", 'class="pure-form"'); ?>
<table class="pure-table pure-table-bordered tight-table" id="plan">
	<thead><tr>
		<th>Tag</th>
		<th>Schicht</th>
<?php foreach ($vehicles as $vehicle): ?>
		<th><?php echo $vehicle ?></th>
<?php endforeach ?>
<?php if ($allow_add !== 'never'): ?>
		<th></th>
<?php endif ?>
	</tr></thead>
	
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
			
			$today		= "";
			$hideable	= "";
			if ($day < $show_day) {
				$hideable = "hideable";
			} else if ($day === $show_day) {
?>
		<tr id="hidden" class="hider"><td colspan="5" id="hider-show"><a href="<?php echo current_url() ?>#show"><i class="fa fa-plus-square-o inline-icon" aria-hidden="true"></i>Alle anzeigen</a></td></tr>
		<tr id="show" class="hider"><td colspan="5" id="hider-hide"><a href="<?php echo current_url() ?>#hidden"><i class="fa fa-minus-square-o inline-icon" aria-hidden="true"></i>Vergangene ausblenden</a></td></tr>
<?php
			}
			if ($day == $cur_day) {
				$today = "today";
			}
			$row_classes = "{$type} {$today} {$hideable}";
			
			echo "\n<tr class='{$row_classes}'>\n";
			echo "<td rowspan={$shift_count} class='day-name' id='day-{$day}'>{$wday_s},<br />$day_s.$month.$year</td>\n";
			
			$i = 0;
			foreach ($disp_times as $time) {
				$shift_id = "{$day}-{$i}";
				if ($shift_id == $allow_add) {
					$add = true;
					$disabled = "selectable";
				}
				
				if ($i !== 0) {
					echo "\n<tr class='{$row_classes}'>\n";
				}
				
				echo "<td class=\"shift-name\"><p>{$time}</p></td>\n";
				
				$j = 0;
				foreach ($vehicles as $vehicle) {
					$slot_id	= "{$day}-{$i}-{$j}";
					
					if (isset($shifts[$slot_id]) && $shifts[$slot_id][0]['duty']['outOfService']
							&& ! isset($shifts[$slot_id][0]['start']) && ! isset($shifts[$slot_id][0]['end'])) {
						$slot_classes = "out-of-service";
					} else {
						$empty = '';
						if (! isset($continuity[$shift_id]) || $continuity[$shift_id] === 0) {
							$empty = "empty-slot";
						} else if ($continuity[$shift_id] < 2) {
							$empty = "alone-slot";
						}
						$slot_classes = "{$disabled} {$empty}";
					}
					
					
					echo "<td class=\"shift-slot {$slot_classes} shift-id-".$shift_id."\">\n";
					$this->load->view('shift_slot', array('slot_id' => $slot_id, 'shift_id' => $shift_id, 'add' => $add));
					echo "</td>\n";
					
					$j++;
				}
				
				if ($add) {
					echo '<td><button type="submit" name="verify" title="Eintragen" class="pure-button secondary-button icon-button fa fa-paper-plane-o"></button></td>'."\n";
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
