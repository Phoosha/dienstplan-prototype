<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plan extends CI_Controller {
	
	
	private $_date_format = 'd.m.Y';
	
	private $_time_format = 'H:i';
	
	private $_time_list = array(
		'00:00' => '00:00',
		'00:30' => '00:30',
		'01:00' => '01:00',
		'01:30' => '01:30',
		'02:00' => '02:00',
		'02:30' => '02:30',
		'03:00' => '03:00',
		'03:30' => '03:30',
		'04:00' => '04:00',
		'04:30' => '04:30',
		'05:00' => '05:00',
		'05:30' => '05:30',
		'06:00' => '06:00',
		'06:30' => '06:30',
		'07:00' => '07:00',
		'07:30' => '07:30',
		'08:00' => '08:00',
		'08:30' => '08:30',
		'09:00' => '09:00',
		'09:30' => '09:30',
		'10:00' => '10:00',
		'10:30' => '10:30',
		'11:00' => '11:00',
		'11:30' => '11:30',
		'12:00' => '12:00',
		'12:30' => '12:30',
		'13:00' => '13:00',
		'13:30' => '13:30',
		'14:00' => '14:00',
		'14:30' => '14:30',
		'15:00' => '15:00',
		'15:30' => '15:30',
		'16:00' => '16:00',
		'16:30' => '16:30',
		'17:00' => '17:00',
		'17:30' => '17:30',
		'18:00' => '18:00',
		'18:30' => '18:30',
		'19:00' => '19:00',
		'19:30' => '19:30',
		'20:00' => '20:00',
		'20:30' => '20:30',
		'21:00' => '21:00',
		'21:30' => '21:30',
		'22:00' => '22:00',
		'22:30' => '22:30',
		'23:00' => '23:00',
		'23:30' => '23:30',
	);
	
	function __construct() {
		parent::__construct();
		$this->load->config('dienstplan', true);
		$this->load->model('plan_model');
		$this->load->model('user_model');
		
		$cal_prefs = array(
			'start_day'		=> $this->config->item('calendar_start_day', 'dienstplan'),
			'month_type'	=> 'long',
			'day_type'		=> 'long',
		);
		$this->load->library('calendar', $cal_prefs);
	}

	/*
	 * Loads the shift view for $month in $year.
	 */
	public function show($year = '', $month = '') {
		if (! $this->ion_auth->logged_in()) {
			$this->session->set_userdata('return_to', current_url());
			redirect('auth/login', 'refresh');
			return;
		}
		
		// Set some common view settings
		$data['title']		= 'Dienstplan';
		$data['menu']		= true;
		$data['menu_id']	= 'plan';
		
		if (! empty($year) && empty($month)) {
			redirect('plan/show', 'redirect');
		}
		
		// Check and set year/month
		$now	 		= time();
		$display_date	= $this->_check_year_month($year, $month, $now);
		$year			= $display_date['year'];
		$month			= $display_date['month'];
		
		// Set current day if we're displaying the current month
		$cur_date	= $this->_get_year_month_day($now);
		$cur_day	= $cur_date['day'];
		$cur_month	= $cur_date['month'];
		$cur_year	= $cur_date['year'];
		$cur_shift	= $this->_get_current_shift_id($year, $month, $now);
		
		$allow_add	= $cur_shift;
		if ($this->ion_auth->is_admin()) {
			$allow_add = '1-0'; // always
		} else if ((int) $year < (int) $cur_year || (int) $month < (int) $cur_month) {
			$allow_add = 'never';
		} 
		
		// Start passing data to the view
		$data['year']		= $year;
		$data['month']		= $month;
		$data['days']		= $this->plan_model->get_days($year, $month);
		$data['day_names']	= $this->calendar->get_day_names();
		$data['vehicles']	= $this->plan_model->get_active_vehicles($now);
		$data['shifts']		= $this->_prepare_view_shifts($year, $month);
		$data['disp_times'] = $this->config->item('shift_display_times', 'dienstplan');
		$data['allow_add']	= $allow_add;
		$data['cur_day']	= (int) explode('-', $cur_shift)[0];
		
		// Setup a calendar object for rendering the navigation
		// This should come last so it doesn't overwrite the calendar
		// from the constructor
		$index_page = index_page();
		$index_page .= ! empty($index_page) ? '/' : '';
		$template = array(
			'table_open' 			=> '<table class="pure-table">',
			'heading_title_cell'	=> '<th colspan="{colspan}"><a href="'. site_url('plan/show') .'"><i class="fa fa-home"></i><br/>{heading}</a></th>',
			'heading_row_start'		=> '<thead id="nav">',
			'heading_row_end'		=> '</thead>',
			'cal_cell_no_content'		=> '<a href="'. current_url() .'#day-{day}">{day}</a>',
			'cal_cell_no_content_today'	=> '<strong><a href="'. current_url() .'#day-{day}">{day}</a></strong>',
		);
		$cal_prefs = array(
			'start_day'			=> $this->config->item('calendar_start_day', 'dienstplan'),
			'month_type'		=> 'long',
			'day_type'			=> 'abr',
			'show_next_prev'	=> true,
			'show_other_days'	=> false,
			'template'			=> $template,
		);
		$this->calendar->initialize($cal_prefs);
		
		$this->load->template('plan', $data);
	}
	
	public function confirm($year = '', $month = '') {
		if (! $this->ion_auth->logged_in()) {
			// no return to for this page
			redirect('auth/login', 'refresh');
			return;
		}
		
		// Set some common view settings
		$data['title']		= 'Dienst eintragen';
		$data['menu']		= true;
		$data['menu_id']	= 'plan';
		
		if (!isset($_POST['verify'])) {
			redirect("plan/show/{$year}/{$month}", 'refresh');
		}
		unset($_POST['verify']); // Don't need this value anymore
		
		if (empty($year) || empty($month)) {
			redirect('plan/show', 'redirect');
		}
		
		// Ensure month/year are valid
		$adjusted_date	= $this->calendar->adjust_date($month, $year);
		$month			= $adjusted_date['month'];
		$year			= $adjusted_date['year'];
		
		// Some values for verifying and using the post data
		$now			= time();
		$shift_times	= $this->config->item('shift_start_times', 'dienstplan');
		$shift_count	= count($shift_times);
		$vehicles		= $this->plan_model->get_active_vehicles($now);
		$vehicle_count	= count($vehicles);
		$total_days		= $this->calendar->get_total_days($month, $year);
		
		// Lists the duty times we generate
		$duties = array();
		
		// This is for duplicate checking between vehicles and shifts
		$slots = array();
				
		foreach ($this->input->post(null) as $shift) {
			
			// Expected format for the slot is: {$day}-{$shift}-{$vehicle}
			$slot_id = explode('-', $shift);
			
			/* ---------------------------------------------------------
			 * First of all some verifications 
			 * -------------------------------------------------------*/
			if (count($slot_id) !== 3) {
				continue;
			}
			
			$day		= (int) $slot_id[0];
			$shift		= (int) $slot_id[1];
			$vehicle	= (int) $slot_id[2];
			
			$slot_id	= "{$day}-{$shift}-{$vehicle}";
			$shift_id	= "{$day}-{$shift}";
			
			if ($day < 1 || $day > $total_days
				|| $shift < 0 || $shift > $shift_count
				|| $vehicle < 0 || $vehicle > $vehicle_count)
			{
				continue;
			}
			
			// The slot might already be taken for another vehicle
			if (isset($slots[$slot_id])) {
				/* TODO: return gracefully with error */
			} else if (isset($slots[$shift_id])) {
				/* TODO: other error */
			} else {
				$slots[$slot_id] = true;
				$slots[$shift_id] = true;
			}
			/* ---------------------------------------------------------
			 * END of verifications
			 * -------------------------------------------------------*/
			
			// Check wheter the next shift belongs to the next day
			$next_day	= false;
			$next_shift = $shift + 1;
			if ($next_shift === $shift_count) {
				$next_day	= true;
				$next_shift = 0;
			}
			
			$start				= $shift_times[$shift];
			$end				= $shift_times[$next_shift];
			$shift_start_time	= strtotime("{$day}-{$month}-{$year} {$start}");
			
			$day = $next_day ? $day + 1 : $day;
			if ($day > $total_days) {
				$day 	 = 1;
				$month	+= 1;
			}
			if ($month > 12) {
				$month	 = 1;
				$year	+= 1;
			}
			$shift_end_time	= strtotime("{$day}-{$month}-{$year} {$end}");
			
			// We need to separate while merging between vehicles
			$start_key	= $shift_start_time .'-'. $vehicle;
			$end_key	= $shift_end_time .'-'. $vehicle;
			
			// combine overlapping shifts
			if (isset($duties[$start_key])) {
				unset($duties[$start_key]);
			} else {
				$duties[$start_key] = array($shift_start_time, $vehicle);
			}
			if (isset($duties[$end_key])) {
				unset($duties[$end_key]);
			} else {
				$duties[$end_key] = array($shift_end_time, $vehicle);
			}
		}
		asort($duties);
		
		$data['duties']		= $duties;
		$data['vehicles']	= $vehicles;
		$data['day_names']	= $this->calendar->get_day_names();
		$data['time_list']	= $this->_time_list;
		$data['users']		= $this->user_model->get_user_names();
		$data['time_format']= $this->_time_format;
		$data['date_format']= $this->_date_format;
		
		if (empty($duties)) {
			redirect("plan/show/{$year}/{$month}", 'refresh');
		} else {
			$this->load->template('confirm_duty', $data);
		}
	}
	
	public function save($year = '', $month = '') {
		if (! $this->ion_auth->logged_in()) {
			// no return to for this page
			redirect('auth/login', 'refresh');
			return;
		}
		
		// Set some common view settings
		$data['title']		= 'Dienst speichern';
		$data['menu']		= true;
		$data['menu_id']	= 'plan';
		
		if (!isset($_POST['save'])) {
			redirect("plan/show/{$year}/{$month}", 'refresh');
		}
		unset($_POST['save']); // Don't need this value anymore
		
		$user_id = $this->ion_auth->get_user_id();
		if ($this->input->post('user_id') && $this->ion_auth->is_admin()) {
			$user_id = $this->input->post('user_id');
			unset($_POST['user_id']);
		}
		
		$duties = $this->_prefix_key_to_subarray($this->input->post(null));
		$fail	= false;
		
		foreach ($duties as $duty) {
			if (empty($duty['start']) || empty($duty['end'])) {
				$fail = true;
				break;
			}
			
			$start	= strtotime($duty['start']);
			$end	= strtotime($duty['end']);
			if (! ($start && $end)) {
				$fail = true;
				break;
			}
			
			$duty['user_id']	= $user_id;
			$duty['start']		= $start;
			$duty['end']		= $end;
			
			if (! $this->plan_model->insert_dutytime($duty)) {
				$fail = true;
				continue;
			}
		}
		
		if ($fail) {
			// TODO: message
		} else {
			// TODO: other message
		}
		redirect("plan/show/{$year}/{$month}", 'redirect');
	}
	
	public function duty($duty_id = null) {
		if (! $this->ion_auth->logged_in()) {
			$this->session->set_userdata('return_to', current_url());
			redirect('auth/login', 'refresh');
			return;
		}
		
		// Set some common view settings
		$data['title']		= 'Dienst bearbeiten';
		$data['menu']		= true;
		$data['menu_id']	= 'plan';
		
		
		$time = time();
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('vehicle', 'Fahrzeug', 'required|is_natural');
		$this->form_validation->set_rules('startdate', 'Dienstanfang', 'required');
		$this->form_validation->set_rules('starttime', 'Dienstanfang', 'required');
		$this->form_validation->set_rules('enddate', 'Dienstende', 'required');
		$this->form_validation->set_rules('endtime', 'Dienstende', 'required');
		$this->form_validation->set_rules('user_id', 'Fahrer', 'required');
		$this->form_validation->set_rules('comment', 'Kommentar', 'max_length[250]');
		if (isset($_POST['modify']) || isset($_POST['delete'])) {
			$this->form_validation->set_rules('id', 'ID', 'required|is_natural');
		} else {
			$this->form_validation->set_rules('add', 'Anfragetyp', 'required');
		}
		
		if ($this->form_validation->run() === true) {
			$start	= strtotime($_POST['startdate'] .' '. $_POST['starttime']);
			$end	= strtotime($_POST['enddate'] .' '. $_POST['endtime']);
			
			if (isset($start) && isset($end) && $start < $end 
					&& (($start > $time) || $this->ion_auth->is_admin())) {
				list($year, $month) = $this->plan_model->check_year_month('', '', $start);
				$vehicles			= $this->plan_model->get_active_vehicles($now);
				$user				= $this->ion_auth->user($_POST['user_id']);
				
				if (isset($vehicles[$_POST['vehicle']]) && isset($user)) {
					$result = null;
					
					if (isset($_POST['delete'])) {
						$result = $this->plan_model->delete_dutytime($_POST['id']);
					} else if (isset($_POST['modify'])) {
						$result = $this->plan_model->replace_dutytime(array(
							'id'		=> $_POST['id'],
							'start'		=> $start,
							'end'		=> $end,
							'vehicle'	=> $_POST['vehicle'],
							'user_id'	=> $_POST['user_id'],
							'comment'	=> $_POST['comment'],
						));;
					} else {
						$result = $this->plan_model->insert_dutytime(array(
							'start'		=> $start,
							'end'		=> $end,
							'vehicle'	=> $_POST['vehicle'],
							'user_id'	=> $_POST['user_id'],
							'comment'	=> $_POST['comment'],
						));
					}
					print_r($result);
					
					/* TODO: check result */
					if ($result) {
						redirect("plan/show/{$year}/{$month}", 'redirect');
					}
				}
			}
		}
		
		$duty_id	= (int) $duty_id;
		$duty		= $this->plan_model->get_duty($duty_id);
		
		if (! isset($duty)) {
			$duty = array(
				'id'		=> '',
				'start'		=> $time,
				'end'		=> $time,
				'vehicle'	=> isset($_POST['vehicle']) ? $_POST['vehicle'] : 0,
				'user_id'	=> isset($_POST['user_id']) ? $_POST['user_id'] : $this->ion_auth->get_user_id(),
				'comment'	=> '',
			);
			$data['title']	= 'Dienst hinzufügen';
		} else {
			$time			= $duty['start'];
			$data['title']	= 'Dienst bearbeiten';
		}
		
		$data = array_merge($data, $duty, array(
			'startdate'	=> isset($_POST['startdate']) ? $_POST['startdate'] : date($this->_date_format, $duty['start']),
			'starttime'	=> isset($_POST['starttime']) ? $_POST['starttime'] : date('H', $duty['start']) .':00',
			'enddate'	=> isset($_POST['enddate']) ? $_POST['enddate'] : date($this->_date_format, $duty['end']),
			'endtime'	=> isset($_POST['endtime']) ? $_POST['endtime'] : date('H', $duty['end']) .':00',
		));
		
		list($year, $month) = $this->plan_model->check_year_month('', '', $time);
		$data['user_names']	= $this->user_model->get_user_names(); /* TODO: active */
		$data['vehicles']	= $this->plan_model->get_active_vehicles($now);
		$data['time_list']	= $this->_time_list;
		
		$this->load->template('duty', $data);
	}
	
	function _prefix_key_to_subarray($a) {
		$ret = array();
		
		foreach ($a as $key => $val) {
			$split	= explode('-', $key, 2);
			if (count($split) !== 2) {
				continue;
			}
			
			$key1	= $split[0];
			$key2	= $split[1];
			if (! isset($ret[$key1])) {
				$ret[$key1] = array();
			}
			$ret[$key1][$key2] = $val;
		}
		
		return $ret;
	}
	
	/*
	 * Generates an array of shifts indexed by the shift slots as used
	 * by the view.
	 */
	function _prepare_view_shifts($year, $month) {
		// To do some calculations we need ints
		$year			= (int) $year;
		$month			= (int) $month;
		
		$shift_times	= $this->config->item('shift_start_times', 'dienstplan');
		$first_shift	= $shift_times[0];
		
		// Increment month by one because the last shift could end in the next month
		$adjusted_date		= $this->calendar->adjust_date($month + 1, $year);
		$end_month			= $adjusted_date['month'];
		$end_year			= $adjusted_date['year'];
		
		// Month start/end as defined by the shifts
		$month_start	= strtotime("01-{$month}-{$year} {$first_shift}");
		$month_end		= strtotime("01-{$end_month}-{$end_year} {$first_shift}");

		// Get the number of days in the given month
		$total_days		= $this->calendar->get_total_days($month, $year);
		// Fetch the unprepared duty times from the model
		$dutytimes		= $this->plan_model->get_dutytimes($month_start, $month_end)->result_array();
		// Array for the resulting shifts as used by the view
		$shifts			= array();
		
		// Fill this array from the dutytimes
		foreach ($dutytimes as $duty) {
			
			// Resolve id to user name
			$duty['user'] 		= $this->user_model->get_full_name($duty['user_id']);
			
			// Set some start and end times
			$duty_start			= $duty['start'];
			$duty_end			= $duty['end'];
			$duty_start_year	= (int) date('Y', $duty_start);
			$duty_end_year		= (int) date('Y', $duty_end);
			$duty_start_month	= (int) date('n', $duty_start);
			$duty_end_month		= (int) date('n', $duty_end);
			$duty_start_day		= (int) date('j', $duty_start);
			$duty_end_day		= (int) date('j', $duty_end);
			
			// Handle start/end day for duties across months or years (unlikely)
			if ($duty_start_month !== $month || $duty_start_year !== $year) {
				$duty_start_day = 1;
			}
			
			if ($duty_end_month !== $month || $duty_end_year !== $year) {
				$duty_end_day = $total_days;
			}
			
			// Start one day earlier because of night shifts
			$duty_start_day = $duty_start_day === 1 ? 1 : $duty_start_day - 1;
			
			// For each day split the duties into shifts as used by the view
			for ($day = $duty_start_day; $day <= $duty_end_day; $day++) {
				$next_day = $day + 1;
				
				$shift_times_count = count($shift_times);
				
				// Loop over the shifts
				for ($i = 0; $i < $shift_times_count; $i++) {
					$i_next		= $i + 1;
					$end_day	= $day;
					
					if ($i_next === $shift_times_count) {
						$i_next = 0;
						$end_day++;
					}
					
					$cur	= $shift_times[$i];
					$next	= $shift_times[$i_next];
					
					$shift_start	= strtotime("{$day}-{$month}-{$year} {$cur}");
					$shift_end		= strtotime("{$end_day}-{$month}-{$year} {$next}");
					$shift			= $this->_prepare_shift($duty, $shift_start, $shift_end, "{$day}-{$i}");
					
					if (isset($shift)) {
						$slot_id			= "{$day}-{$i}-{$duty['vehicle']}";
						$shifts[$slot_id][]	= $shift;
					}
				}
			}
		}
		
		return $shifts;
	}
	
	/*
	 * Returns a shift entry as used by the view for displaying the shift.
	 * Additionally to the duty start and end times are returned as strings.
	 * 
	 * Format:
	 * 	duty, start, end
	 */
	function _prepare_shift($duty, $shift_start, $shift_end, $shift_id) {
		// Grab these values for easier access
		$duty_start	= $duty['start'];
		$duty_end	= $duty['end'];
			
		// These three are determined for each shift
		$provides	= false;	// Set to true if this shift is provided by the duty
		$real_start	= null;		// Set for start time differing from the shift's one
		$real_end	= null; 	// Likewise for end time
		
		if ($duty_start <= $shift_start
				&& $duty_end >= $shift_end) {
			// duty provides for whole shift
			$provides = true;
			
		} else {
			if ($duty_start > $shift_start
					&& $duty_start < $shift_end) {
				// duty starts within the shift
				$provides 	= true;
				
				$duty_start_hour	= date('G', $duty_start);
				$duty_start_minute	= date('i', $duty_start);
				$real_start = ((int) $duty_start_minute) === 0
						? $duty_start_hour
						: $duty_start_hour .':'. $duty_start_minute;
			}
			
			if ($duty_end > $shift_start
					&& $duty_end < $shift_end) {
				// duty ends within the shift
				$provides 	= true;
				
				$duty_end_hour		= date('G', $duty_end);
				$duty_end_minute	= date('i', $duty_end);
				$real_end = ((int) $duty_end_minute) === 0
						? $duty_end_hour
						: $duty_end_hour .':'. $duty_end_minute;
			}
		}
		
		// Now finally add a providing duty to the shift
		if ($provides) {
			return array(
				'duty'		=> $duty,
				'start'		=> $real_start,
				'end'		=> $real_end,
			);
		}
	}
	
	/*
	 * Returns the start time of the first shift.
	 */
	function _get_first_shift_start($year, $month, $day = '01') {
		$shift_times	= $this->config->item('shift_start_times', 'dienstplan');
		return strtotime("{$day}-{$month}-{$year} {$shift_times[0]}");
	}
	
	/*
	 * Returns the year and month for $time.
	 */
	function _get_year_month_day($time) {
		$year			= date('Y', $time);
		$month			= (int) date('n', $time);
		$day			= date('j', $time);
		$first_shift	= $this->_get_first_shift_start($year, $month);
		
		$date	 		= $this->calendar->adjust_date($month, $year);
		
		// Handle last shift reaching into next month
		if ($time <= $first_shift) {
			$month	-= 1;
			$date	 = $this->calendar->adjust_date($month, $year);
			$day	 = $this->calendar->get_total_days($date['month'], $date['year']);
		}
		$date['day'] = $day;
		
		return $date;
	}
	
	/*
	 * Returns valid values for year and month. If unset the values are
	 * set from $time.
	 */
	function _check_year_month($year = '', $month = '', $time = null) {
		if (! isset($time)) {
			$time = time();
		}
		
		// Fall back to passed time if year unset
		if (empty($year) || empty($month)) {
			$date	= $this->_get_year_month_day($time);
			$year	= $date['year'];
			$month	= $date['month'];
		}
		
		// Ensure the date is a valid value
		return $this->calendar->adjust_date($month, $year);
	}
	
	function _get_current_shift_id($year, $month, $now) {
		$cur_date		= $this->_get_year_month_day($now);
		$shift_times	= $this->config->item('shift_start_times', 'dienstplan');
		
		$cur_shift = '0-0';
		if ($cur_date['year'] ===  $year) {
			if ($cur_date['month'] === $month) {
				$i = 0;
				// By default assume we're still in yesterdays shift
				$cur_shift = ($cur_date['day'] - 1) .'-'. (count($shift_times) - 1);
				
				foreach ($shift_times as $cur) {
					$shift_start = strtotime("{$cur_date['day']}-{$cur_date['month']}-{$cur_date['year']} {$cur}");
					if ($shift_start < $now) {
						// shift start is not past so use the first match
						$cur_shift = $cur_date['day'] .'-'. $i;
					}
					$i++;
				}
			}
		}
		
		return $cur_shift;
	}
	
}
