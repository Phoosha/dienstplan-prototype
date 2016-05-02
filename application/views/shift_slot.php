<?php defined('BASEPATH') OR exit('No direct script access allowed');


if (isset($shifts[$slot_id])) {
	$out_of_service = $shifts[$slot_id][0]['duty']['outOfService'];
	
	foreach ($shifts[$slot_id] as $shift) {
		echo "<p>\n";
		
		
		echo '<span class="duty-time">';
		if (isset($shift['start']) && isset($shift['end'])) {
			echo "Von {$shift['start']} bis {$shift['end']} Uhr<br/>\n";
		} else if (isset($shift['start'])) {
			echo "Ab {$shift['start']} Uhr<br/>\n";
		} else if (isset($shift['end'])) {
			echo "Bis {$shift['end']} Uhr<br/>\n";
		}
		echo "</span>\n";
		
		if ($out_of_service) {
			echo "<span class=\"out-of-service\">Außer Dienst<i class=\"fa fa-wrench inline-icon\" aria-hidden=\"true\"></i></span><br/>\n";
		}
		
		if (! $out_of_service || $this->ion_auth->is_admin()) {
			echo '<span class="duty-user">';
				if (! $shift['duty']['locked']) {
					echo '<a href='. site_url('plan/duty/'. $shift['duty']['id']) .">";
				}
				echo $shift['duty']['user'];
				if (! $shift['duty']['locked']) {
					echo '</a>';
				}
				if (! $shift['duty']['mayDrive']) {
					echo '<i class="fa fa-exclamation-triangle inline-icon" aria-hidden="true"></i>';
				}
			echo "</span><br/>\n";
		}
		
		

		echo "<span class=\"duty-comment\">\n";
		if (! $shift['duty']['mayDrive'] && ! $shift['duty']['hasDriver']) {
			echo "<span class=\"duty-need-driver\">Braucht Fahrer!</span><br/>\n";
		}
		if ($shift['duty']['internee']) {
			echo "mit Praktikant<br/>\n";
		}
		echo $shift['duty']['comment'];
		echo "</span>\n";;
		
		echo "</p>\n";
	}
} else {
	echo "<p></p>";
}
if ($add) {
	echo form_checkbox('shift-'.$shift_id, $slot_id, false, 'class="shift-slot-select"');
}
