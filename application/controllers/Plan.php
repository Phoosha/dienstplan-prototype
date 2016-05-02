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
		'24:00' => '24:00',
	);
	
	function __construct() {
		parent::__construct();
		$this->load->config('dienstplan', true);
		$this->load->model('plan_model');
		$this->load->model('user_model');
		$this->load->helper('form');
		
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
		
		// try to get a message about what went wrong if anything
		$data['message'] = $this->session->flashdata('message');
		
		if (! empty($year) && empty($month)) {
			redirect('plan/show', 'redirect');
		}
		
		// Check and set year/month
		$now	 		= time();
		$display_date	= $this->_check_year_month($now, $year, $month);
		$year			= $display_date['year'];
		$month			= $display_date['month'];
		
		// Set current day if we're displaying the current month
		$cur_date	= $this->_get_year_month_day($now);
		$cur_month	= $cur_date['month'];
		$cur_year	= $cur_date['year'];
		$cur_shift	= $this->_get_current_shift_id($year, $month, $now);
		$cur_day	= explode('-', $cur_shift)[0]; // might differ from now's day
		
		$allow_add	= $cur_shift === '0-0' ? '1-0' : $cur_shift;
		if ($this->ion_auth->is_admin()) {
			$allow_add = '1-0'; // always
		} else if ($year < $cur_year || $month < $cur_month) {
			$allow_add = 'never';
		}
		
		$show_day = $cur_day - $this->config->item('visible_past_days', 'dienstplan');
		if ($show_day <= $this->config->item('hide_days_threshold', 'dienstplan')) {
			$show_day = 0;
		}
		
		list($shifts, $continuity) = $this->_prepare_view_shifts($year, $month);
		
		// Start passing data to the view
		$data['year']		= $year;
		$data['month']		= $month;
		$data['days']		= $this->plan_model->get_days($year, $month);
		$data['day_names']	= $this->calendar->get_day_names();
		$data['vehicles']	= $this->plan_model->get_active_vehicles($this->_get_last_shift_end($year, $month));
		$data['shifts']		= $shifts;
		$data['continuity']	= $continuity;
		$data['disp_times'] = $this->config->item('shift_display_times', 'dienstplan');
		$data['allow_add']	= $allow_add;
		$data['cur_day']	= $cur_day;
		$data['show_day']	= $show_day;
		
		// Setup a calendar object for rendering the navigation
		// This should come last so it doesn't overwrite the calendar
		// from the constructor
		$index_page = index_page();
		$index_page .= ! empty($index_page) ? '/' : '';
		$template = array(
			'table_open' 			=> '<table class="pure-table">',
			'heading_title_cell'	=> '<th colspan="{colspan}"><a href="'. site_url('plan/show') .'"><i class="fa fa-home fa-lg linked-icon"></i><br/>{heading}</a></th>',
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
			if (isset($slots[$slot_id]) || isset($slots[$shift_id])) {
				continue;  // just ignore it since we just display a confirmation page
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
		
		$data['year']		= $year;
		$data['month']		= $month;
		$data['duties']		= $duties;
		$data['vehicles']	= $vehicles;
		$data['day_names']	= $this->calendar->get_day_names();
		$data['time_list']	= $this->_time_list;
		$data['users']		= $this->user_model->get_user_names('members');
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
		
		if (! isset($_POST['save'])) {
			redirect("plan/show/{$year}/{$month}", 'refresh');
		}
		unset($_POST['save']); // Don't need this value anymore
		
		$user_id = $this->ion_auth->get_user_id();
		if ($this->input->post('user_id') && $this->ion_auth->is_admin()) {
			$user_id = $this->input->post('user_id');
			unset($_POST['user_id']);
		}
		
		$with_internee = false;
		if ($this->input->post('internee')) {
			$with_internee = $this->input->post('internee');
			unset($_POST['internee']);
		}
		
		$out_of_service = false;
		if ($this->input->post('outOfService')) {
			$out_of_service = $this->input->post('outOfService');
			unset($_POST['outOfService']);
		}
		
		$duties 		= $this->_prefix_key_to_subarray($this->input->post(null));
		$insert_duties	= array();
		$fail			= false;
		
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
			$duty['internee']	= $with_internee;
			$duty['outOfService'] = $out_of_service;
			
			$insert_duties[]	= $duty;
		}
		
		if ($fail) {
			$this->session->set_flashdata('message', $this->plan_model->errors() ? $this->plan_model->errors() : '<p class="error">Ungültige Dienstzeiten</p>');
		}
		
		if ($this->plan_model->insert_batch_dutytimes($insert_duties)) {
			$this->session->set_flashdata('message', '<p class="success">Dienst(e) wurde(n) erfolgreich eingetragen</p>');
		} else {
			$this->session->set_flashdata('message', $this->plan_model->errors());
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
		$data['datepicker']	= true;
		
		$now		= time();
		$message	= null;
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		if (isset($_POST['add']) || isset($_POST['modify'])) {
			// modify and add require more values
			$this->form_validation->set_rules('vehicle', 'Fahrzeug', 'required|is_natural');
			$this->form_validation->set_rules('startdate', 'Dienstanfang', 'required');
			$this->form_validation->set_rules('starttime', 'Dienstanfang', 'required|exact_length[5]');
			$this->form_validation->set_rules('enddate', 'Dienstende', 'required');
			$this->form_validation->set_rules('endtime', 'Dienstende', 'required|exact_length[5]');
			$this->form_validation->set_rules('user_id', 'Fahrer', 'required|is_natural');
			$this->form_validation->set_rules('comment', 'Kommentar', 'max_length[100]|trim|htmlspecialchars');	
		} else {
			$this->form_validation->set_rules('delete', 'Anfragetyp', 'required');
		}
		
		// We just require some basic parameters here and leave the checking to the model
		if ($this->form_validation->run() === true) {
				
			$result = null; // result of the performed action
			$time	= $now; // time to use for redirecting the user
			
			// Determine which action to perform	
			if (isset($_POST['delete'])) {
				$result = $this->plan_model->delete_dutytime($duty_id);
				
				if ($result) {
					$this->session->set_flashdata('message', '<p class="success">Dienst wurde erfolgreich gelöscht</p>');
				}
				
			} else {
				$start	= strtotime($this->input->post('startdate') .' '. $this->input->post('starttime'));
				$end	= strtotime($this->input->post('enddate') .' '. $this->input->post('endtime'));
				$time	= $start;
				
				if (isset($_POST['modify'])) {
					$result = $this->plan_model->replace_dutytime(array(
						'id'		=> $duty_id,
						'start'		=> $start,
						'end'		=> $end,
						'vehicle'	=> $this->input->post('vehicle'),
						'user_id'	=> $this->input->post('user_id'),
						'comment'	=> $this->input->post('comment'),
						'internee'  => $this->input->post('internee'),
					));
				
					if ($result) {
						$this->session->set_flashdata('message', '<p class="success">Dienst wurde erfolgreich geändert</p>');
					}
				} else {
					$result = $this->plan_model->insert_dutytime(array(
						'start'		=> $start,
						'end'		=> $end,
						'vehicle'	=> $this->input->post('vehicle'),
						'user_id'	=> $this->input->post('user_id'),
						'comment'	=> $this->input->post('comment'),
						'internee'  => $this->input->post('internee'),
					));
					
					if ($result) {
						$this->session->set_flashdata('message', '<p class="success">Dienst wurde erfolgreich eingefügt</p>');
					}
				}
			}
			
			$display_date	= $this->_check_year_month($time);
			$year			= $display_date['year'];
			$month			= $display_date['month'];
			
			if ($result) {
				redirect("plan/show/{$year}/{$month}", 'redirect');
				return;
			} else {
				$message = $this->plan_model->errors();
			}
		}
			
		// try to get a message about what went wrong if anything
		$data['message'] = validation_errors() ? 
			validation_errors() : $message;
		
		if ($duty_id !== null) {
			$duty_id	= round($duty_id);
			$duty		= $this->plan_model->get_duty($duty_id);
		}
		
		if (! isset($duty)) {
			$data['title']	= 'Dienst hinzufügen';
			
			$duty = array(
				'id'		=> '',
				'start'		=> $now,
				'end'		=> $now,
				'comment'	=> '',
				'user_id'	=> $this->ion_auth->get_user_id(),
				'vehicle'	=> 0,
				'internee'	=> false,
			);
		} else {
			$data['title']	= 'Dienst bearbeiten';
		}
		
		$use_post = array('startdate', 'starttime', 'enddate', 'endtime', 'vehicle', 'comment', 'vehicle', 'user_id');
		
		$submitted = array();
		foreach ($use_post as $k) {
			if ($this->input->post($k) !== null) {
				$submitted[$k] = $this->input->post($k);
			}
		}
		
		if (isset($_POST['add']) || isset($_POST['modify'])) {
			$submitted['internee'] = isset($_POST['internee']);
		}
		
		$data = array_merge($data, $duty, array(
					'startdate'	=> date($this->_date_format, $duty['start']),
					'starttime'	=> date('H', $duty['start']) .':00',
					'enddate'	=> date($this->_date_format, $duty['end']),
					'endtime'	=> date('H', $duty['end']) .':00',
				), $submitted);
		
		$data['user_names']	= $this->user_model->get_user_names('members');
		$data['vehicles']	= $this->plan_model->get_active_vehicles($now);
		$data['time_list']	= $this->_time_list;
		
		$this->load->template('duty', $data);
	}
	
	/*
	 * Splits the array $a into subarrays by spliting the original keys.
	 */
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
		
		$shift_times_count	= count($this->config->item('shift_start_times', 'dienstplan'));
		
		// Get the number of days in the given month
		$total_days		= $this->calendar->get_total_days($month, $year);
		
		// Month start/end as defined by the shifts
		$month_start	= $this->_get_first_shift_start($year, $month);
		$month_end		= $this->_get_last_shift_end($year, $month);
		
		// Fetch the unprepared duty times from the model
		$dutytimes		= $this->plan_model->get_dutytimes($month_start, $month_end)->result_array();
		
		// Array for the resulting shifts as used by the view
		$shifts			= array();
		// Array of starts and ends for continuity checking per shift
		$starts			= array();
		$ends			= array();
		
		// Fill this array from the dutytimes
		foreach ($dutytimes as $duty) {
			
			// Resolve id to user name
			$duty['user'] 		= $this->user_model->get_full_name($duty['user_id']);
			if (! $duty['mayDrive']) {
				$duty['hasDriver'] = $this->plan_model->has_driver($duty);
			}
			
			// Set some start and end times
			$duty_start			= $duty['start'];
			$duty_end			= $duty['end'];
			$duty_start_year	= date('Y', $duty_start);
			$duty_end_year		= date('Y', $duty_end);
			$duty_start_month	= date('n', $duty_start);
			$duty_end_month		= date('n', $duty_end);
			$duty_start_day		= date('j', $duty_start);
			$duty_end_day		= date('j', $duty_end);
			
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
				
				// Loop over the shifts
				for ($i = 0; $i < $shift_times_count; $i++) {
					$i_next		= ($i + 1) % $shift_times_count;
					
					$shift_id		= "{$day}-{$i}";
					$shift_start	= $this->_get_shift_start($year, $month, $shift_id);
					$shift_end		= $this->_get_shift_end($year, $month, $shift_id);
					
					$shift			= $this->_prepare_shift($duty, $shift_start, $shift_end, $shift_id);
					
					if (isset($shift)) {
						
						$slot_id				= "{$day}-{$i}-{$duty['vehicle']}";						
						$shifts[$slot_id][]		= $shift;
						if (! $duty['outOfService'] && ($duty['mayDrive'] || $duty['hasDriver'])) {
							$starts[$shift_id][]	= $duty['start'];
							$ends[$shift_id][]		= $duty['end'];
						}
					}
				}
			}
		}
		
		// Array for the continuity per shift
		$continuity = array();
		foreach (array_keys($starts) as $shift_id) {
			$shift_start	= $this->_get_shift_start($year, $month, $shift_id);
			$shift_end		= $this->_get_shift_end($year, $month, $shift_id);
			
			$continuity[$shift_id] = $this->_determine_continuation(
				array($shift_start, $shift_start), $shift_end, $starts[$shift_id], $ends[$shift_id]);
		}
		
		return array($shifts, $continuity);
	}
	
	function _get_shift_start($year, $month, $shift_id) {
		$v		= explode('-', $shift_id, 2);
		$day	= $v[0];
		$shift	= $v[1];
		
		$shift_times = $this->config->item('shift_start_times', 'dienstplan');
		
		return strtotime("{$day}-{$month}-{$year} {$shift_times[$shift]}");
	}
	
	function _get_shift_end($year, $month, $shift_id) {
		$v		= explode('-', $shift_id, 2);
		$day	= $v[0];
		$shift	= $v[1] + 1;
		
		$shift_times = $this->config->item('shift_start_times', 'dienstplan');
		
		if ($shift == count($shift_times)) {
			$shift = 0;
			list($year, $month, $day) = $this->_increment_day($year, $month, $day);
		}
		
		return strtotime("{$day}-{$month}-{$year} {$shift_times[$shift]}");
	}
	
	function _increment_day($year, $month, $day) {
		$day++;
		
		if ($day > $this->calendar->get_total_days($month, $year)) {
			$day = 1;
			list($year, $month) = $this->_increment_month($year, $month);
		}
		
		return array($year, $month, $day);
	}
	
	function _increment_month($year, $month) {
		$month++;
		
		if ($month > 12) {
			$month = 1;
			$year++;
		}
		
		return array($year, $month);
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
				$real_start = $duty_start_minute == 0
						? $duty_start_hour
						: $duty_start_hour .':'. $duty_start_minute;
			}
			
			if ($duty_end > $shift_start
					&& $duty_end < $shift_end) {
				// duty ends within the shift
				$provides 	= true;
				
				$duty_end_hour		= date('G', $duty_end);
				$duty_end_minute	= date('i', $duty_end);
				$real_end = $duty_end_minute == 0
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
	 * Returns the start time of the first shift of the given day or if
	 * day is unset of the month's first day.
	 */
	function _get_first_shift_start($year, $month, $day = '01') {
		$shift_times	= $this->config->item('shift_start_times', 'dienstplan');
		return strtotime("{$day}-{$month}-{$year} {$shift_times[0]}");
	}
	
	/*
	 * Returns the end time of the last shift of the given day or if day
	 * is unset of the month's last day.
	 */
	function _get_last_shift_end($year, $month, $day = null) {
		if (! $day) {
			$day = $this->calendar->get_total_days($month, $year);
		}
		
		// last shift's end is next day's first shift's start
		list($year, $month, $day) = $this->_increment_day($year, $month, $day);
		
		return $this->_get_first_shift_start($year, $month, $day);
	}
	
	/*
	 * Returns the year and month and day for $time based on the
	 * associated shift.
	 */
	function _get_year_month_day($time) {
		$year			= date('Y', $time);
		$month			= date('n', $time);
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
	function _check_year_month($time = null, $year = '', $month = '') {
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
		return $this->_adjust_date_for_unix($year, $month);
	}

	/*
	 * Returns valid values for year and month, which are in between
	 * the year 1970 and 2105 for an unsigned 32 bit unix timestamp.
	 */
	function _adjust_date_for_unix($year, $month) {
		if ($year < 0) {
			$year = 2105 + ($year % 136);
		} else if ($year < 1970 || $year > 2105) {
			$year = 1970 + ($year % 136);
		}

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
	
	/*
	 * Checks whether there are whole numberedly continous unions of
	 * the invervals given by $starts and $ends associatively. Each such
	 * union is constructed for exactly one offset from $offsets and
	 * must contain both the offset and the target value.
	 *
	 * Returns: Number n of such unions (i.e. 0 <= n <= count($offsets)
	 *
	 */
	function _determine_continuation($offsets, $target_value, $starts, $ends) {

		// Work linearly from the smallest value to the biggest
		array_multisort($starts, SORT_ASC|SORT_NUMERIC, $ends);
		sort($offsets, SORT_NUMERIC);
		
		/*
		 * Update the values we could reach in $offsets as long as we
		 * could reach any and as long as we have still values in $starts
		 *  resp. $ends to update it with. Additionally we stop once we
		 * all offsets have reached the target value.
		 */
		while (! empty($offsets) && ! empty($starts) && $offsets[0] < $target_value) {
			$start	= array_shift($starts);
			$end	= array_shift($ends);
			
			while ($smallest_offset = array_shift($offsets)) {
				if ($start <= $smallest_offset) {
					$offsets[] = $end;
					break;
				}
			}
			
			sort($offsets, SORT_NUMERIC);
		}
		
		// Now remove all offsets which did not reach the target value
		while (! empty($offsets) && $offsets[0] < $target_value) {
			array_shift($offsets);
		}
		
		return count($offsets);
	}
	
}
