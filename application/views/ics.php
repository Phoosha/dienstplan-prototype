<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
BEGIN:VCALENDAR
PRODID:<?php echo site_url(); ?>

VERSION:2.0
CALSCALE:GREGORIAN
X-WR-CALNAME:FRS <?php echo $name; ?>

X-WR-TIMEZONE:<?php echo date('e') ?>

<?php foreach ($duties as $duty): 
	if ($duty['outOfService']) continue; ?>
BEGIN:VEVENT
UID:<?php echo md5($duty['id'].$domain.'9gdh0ja0ghjwq').'@'.$domain ?>

DTSTAMP:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['modified_on'])); ?>

DTSTART:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['start'])); ?>

DTEND:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['end'])); ?>

LAST-MODIFIED:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['modified_on'])); ?>

SUMMARY:Dienst FRS
LOCATION: <?php echo $vehicles[$duty['vehicle']] ?>, Hohenbrunn
END:VEVENT
<?php endforeach ?>
END:VCALENDAR
