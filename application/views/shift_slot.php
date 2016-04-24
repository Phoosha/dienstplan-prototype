<?php defined('BASEPATH') OR exit('No direct script access allowed');


if (isset($shifts[$slot_id])) {
	foreach ($shifts[$slot_id] as $shift) {
		echo "<p>\n";
		
		
		echo '<span class="duty-time">';
		if (isset($shift['start']) && isset($shift['end'])) {
			echo "Von {$shift['start']} bis {$shift['end']} Uhr<br/>";
		} else if (isset($shift['start'])) {
			echo "Ab {$shift['start']} Uhr<br/>";
		} else if (isset($shift['end'])) {
			echo "Bis {$shift['end']} Uhr<br/>";
		}
		echo "</span>\n";
		
		echo '<span class="duty-user">';
			if (! $shift['duty']['locked']) {
				echo '<a href='. site_url('plan/duty/'. $shift['duty']['id']) .">\n";
			}
			echo $shift['duty']['user'];
			if (! $shift['duty']['locked']) {
				echo '</a>';
			}
		echo "</span><br/>\n";;

		echo "<span class=\"duty-comment\">{$shift['duty']['comment']}</span>\n";
		
		echo "</p>\n";
	}
} else {
	echo "<p></p>";
}
if ($add) {
	echo form_checkbox('shift-'.$shift_id, $slot_id, false, 'class="shift-slot-select"');
}
