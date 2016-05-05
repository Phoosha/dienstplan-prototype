<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
BEGIN:VCALENDAR
PRODID:<?php echo site_url(); ?>

VERSION:2.0
METHOD:PUBLISH
CALSCALE:GREGORIAN
X-WR-CALNAME:<?php echo $name; ?>

X-WR-TIMEZONE:<?php echo date('e') ?>

<?php foreach ($duties as $duty): 
	if ($duty['outOfService']) continue; ?>
BEGIN:VEVENT
UID:<?php echo md5($duty['id'].$domain.$cal_seed).'@'.$domain ?>

DTSTAMP:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['modified_on'])); ?>

DTSTART:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['start'])); ?>

DTEND:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['end'])); ?>

LAST-MODIFIED:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['modified_on'])); ?>

CREATED:<?php echo date('Ymd\THis\Z', local_to_gmt($duty['created_on'])); ?>

SEQUENCE:<?php echo $duty['sequence']; ?>

SUMMARY:Dienst FRS
LOCATION:<?php echo $vehicles[$duty['vehicle']] ?>, Hohenbrunn
DESCRIPTION:<?php
	if ($duty['internee']) {
		echo 'Mit Praktikant. ';
	}
	echo $duty['comment'] . ' ';
	if (! $duty['mayDrive']) {
		echo "Du darfst nicht alleine fahren.";
	}
?>

STATUS:CONFIRMED
TRANSP:TRANSPARENT
END:VEVENT
<?php endforeach ?>
END:VCALENDAR<?php ?>
