<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// The day on which a calendar week should begin
$config['calendar_start_day']	= 'monday';
$config['shift_start_times']	= array("06:00:00", "18:00:00");
$config['shift_display_times']	= array("6&nbsp;&#8211;&nbsp;18 Uhr", "18&nbsp;&#8211;&nbsp;6 Uhr");

$config['lock_duty_threshold']	= 604800;

$config['visible_past_days']	= 1;
$config['hide_days_threshold']	= 2;

$config['calendar_domain']		= "localhost";
$config['calendar_uid_seed']	= "cae9ec6276d4e1c67bf64173024ad40e";
$config['calendar_start_dist']	= 2678400; // 60 * 60 * 24 * 31 seconds
$config['calendar_end_dist']	= 2678400; // 60 * 60 * 24 * 31 seconds
