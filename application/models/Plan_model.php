<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_model extends CI_Model {
	
	protected $errors;
	
	protected $error_messages = array(
		'start_before_end'
			=> 'Die Anfangszeit des Diensts darf nicht vor der Endzeit liegen',
		'conflicting_duty' 
			=> 'Dienst überschneidet sich mit einem deiner anderen Dienste',
		'no_permission_to_add'
			=> 'Du darfst dich für diesen Dienst nicht mehr eintragen',
		'no_permission_to_edit'
			=> 'Du darfst diesen Dienst nicht mehr verändern oder löschen',
		'nonexistant_duty'
			=> 'Dieser Dienst existiert nicht mehr',
		'conflicting_insert'
			=> 'Du darfst dich nicht für mehrere Dienste gleichzeitig eintragen',
	);
	
	protected $error_start_delimiter	= '<p class="error">';
	protected $error_end_delimiter		= '</p>';
	
	public function __construct()
	{
		$errors = array();
		$this->load->database();
	}
	
	/*
	 * Returns an array of days and corresponding week days for the
	 * given month and year.
	 * Each entry is of the form:
	 * 		array(	'day'	=> <day number>
	 * 				'wday'	=> <week day>
	 */
	public function get_days($year = '', $month = '') {
		$local_time = time();

		// Determine the total days in the month
		$total_days = $this->calendar->get_total_days($month, $year);

		// Set the starting week day of the month
		$local_date	= mktime(12, 0, 0, $month, 1, $year);
		$date		= getdate($local_date);
		$wday 		= $date['wday'];
		
		$days = array();
		for ($day = 1; $day <= $total_days; $day++) {
			$day_s = strval($day);
			if (strlen($day_s) === 1) {
				$day_s = '0'.$day_s;
			}
			$days[] = array(
				'day'	=> $day_s,
				'wday'	=> $wday,
			);
			$wday = ($wday + 1) % 7;
		}
		
		return $days;
	}
	
	/*
	 * Returns the valid vehicle names for $month in $year.
	 */
	public function get_active_vehicles($time) {
		// fetch the newes entry which is already active
		$this->db->select('vehicle_0, vehicle_1');
		$this->db->where('active_on <', $time);
		$this->db->limit(1);
		$this->db->order_by('active_on DESC');
		$vehicles = $this->db->get('vehicles')->row_array();
		
		// set some default values for no or incomplete results
		if (empty($vehicles)) {
			$vehicles = array('Fzg. 1', 'Fzg. 2');;
		} else {
			$vehicles = array_values($vehicles);
			foreach ($vehicles as $k => $v) {
				if (empty($v)) {
					$vehicles[$k] = 'Fzg. ' . ($k + 1);
				}
			}
		}
		
		return $vehicles;
	}
	
	/*
	 * DB select statement for duties
	 */
	private function _duty_select() {
		$locked_before	= $this->get_lock_end_time();
		$user_id		= $this->ion_auth->get_user_id();
		
		if ($this->ion_auth->is_admin()) {
			$this->db->select("*, 0 AS locked");
		} else {
			$this->db->select("*, (start <= {$locked_before} OR user_id <> {$user_id}) AS locked");
		}
	}
	
	/*
	 * Returns the duty with $id.
	 */
	public function get_duty($id) {
		if (! $id) {
			return null;
		}
		
		$locked_before = $this->get_lock_end_time();
		
		$this->_duty_select();
		$this->db->where('id', $id);
		return $this->db->get('dutytimes')->row_array();
	}
	
	/*
	 * Returns all relevant duties starting with the first shift of the
	 * passed $month in $year and ending with the last one.
	 * 
	 * Format:
	 * 	id, start, end, comment, user_id, vehicle, locked
	 */
	public function get_dutytimes($start, $end) {		
		$this->_duty_select();
		// 1. All duties containing the intervall
		$this->db->group_start();
			$this->db->where('start <=', $start);
			$this->db->where('end >=', $end);
		$this->db->group_end();
		// 2. All duties which start within
		$this->db->or_group_start();
			$this->db->where('start >', $start);
			$this->db->where('start <', $end);
		$this->db->group_end();
		// 3. All duties which end within
		$this->db->or_group_start();
			$this->db->where('end >', $start);
			$this->db->where('end <', $end);
		$this->db->group_end();	
		
		$this->db->order_by('start ASC, end ASC');
		$query = $this->db->get('dutytimes');
		
		return $query;
	}
	
	/*
	 * Returns all duties of $user_id overlapping with $start and $end
	 * times.
	 */
	public function get_conflicting_dutytimes($user_id, $start, $end, $duty_id = null) {	
		$this->_duty_select();
		if ($duty_id) {
			$this->db->where('id !=', $duty_id);
		}
		$this->db->where('user_id', $user_id);
		$this->db->group_start();
			// 1. All duties containing this interval
			$this->db->or_group_start();
				$this->db->where('start <=', $start);
				$this->db->where('end >=', $end);
			$this->db->group_end();
			// 2. All duties which start in this interval
			$this->db->or_group_start();
				$this->db->where('start >', $start);
				$this->db->where('start <', $end);
			$this->db->group_end();
			// 3. All duties which end in this interval
			$this->db->or_group_start();
				$this->db->where('end >', $start);
				$this->db->where('end <', $end);
			$this->db->group_end();
		$this->db->group_end();
		
		$this->db->order_by('start ASC, end ASC');
		$query = $this->db->get('dutytimes');
		
		return $query;
	}
	
	/*
	 * Inserts $duty into the DB.
	 */
	public function insert_dutytime($duty) {
		
		if (! $this->check_insert_dutytime($duty)) {
			return false;
		}
		
		return $this->db->insert('dutytimes', $duty);
	}
	
	/*
	 * Batch inserts multiples duties into the DB as one transaction.
	 */
	public function insert_batch_dutytimes($duties) {
		
		foreach ($duties as &$duty) {
			if (! $this->check_insert_dutytime($duty)) {
				return false;
			}
		}

		if (! $this->are_overlap_free($duties)) {
			$this->set_error('conflicting_insert');
			return false;
		}
		
		return $this->db->insert_batch('dutytimes', $duties);
	}
	
	/*
	 * Checks whether $duty is a valid duty for insertion.
	 */
	public function check_insert_dutytime(&$duty) {
		
		if (! $this->validate_dutytime($duty)) {
			return false;
		}
		
		if (! $this->is_allowed_to_add($duty)) {
			$this->set_error('no_permission_to_add');
			return false;
		}
		
		if (! $this->is_not_conflicting($duty)) {
			$this->set_error('conflicting_duty');
			return false;
		}
		
		return true;
	}
	
	/*
	 * Deletes the duty with $id.
	 */
	public function delete_dutytime($id) {
		
		if (! $this->is_existing_duty_id($id)) {
			$this->set_error('nonexistant_duty');
			return false;
		}
		
		if (! $this->is_allowed_to_edit($id)) {
			$this->set_error('no_permission_to_edit');
			return false;
		}
		
		return $this->db->delete('dutytimes', array('id' => $id));
	}
	
	/*
	 * Replaces the duty in the DB with $duty.
	 */
	public function replace_dutytime($duty) {
		
		if (! $this->validate_dutytime($duty, true)) {
			return false;
		}
		
		if (! $this->is_existing_duty_id($duty['id'])) {
			$this->set_error('nonexistant_duty');
			return false;
		}
		
		if (! $this->is_allowed_to_edit($duty['id'])) {
			$this->set_error('no_permission_to_edit');
			return false;
		}
		
		if (! $this->is_allowed_to_add($duty)) {
			$this->set_error('no_permission_to_add');
			return false;
		}
		
		if (! $this->is_not_conflicting($duty)) {
			$this->set_error('conflicting_duty');
			return false;
		}
		
		return $this->db->replace('dutytimes', $duty);
	}
	
	/*
	 * Returns the time starting from which a duty is locked for edits.
	 */
	public function get_lock_end_time($time = null) {
		if (! isset($time)) {
			$time = time();
		}
		return $time + $this->config->item('lock_duty_threshold', 'dienstplan');
	}
	
	/*
	 * Validates all values of $duty and optionally with $check_id the
	 * id field. Additionally all values are converted to int's.
	 */
	public function validate_dutytime(&$duty, $check_id = false) {
		if (! isset($duty['start']) || ! isset($duty['end'])
				|| ! isset($duty['vehicle']) || ! isset($duty['user_id'])) {
			return false;
		}
		
		if ($check_id && ! isset($duty['id'])) {
			return false;
		}
		
		if ($check_id) {
			$duty['id']		= round($duty['id']);
		}
		$duty['start']		= round($duty['start']);
		$duty['end']		= round($duty['end']);
		$duty['vehicle']	= (int) $duty['vehicle'];
		$duty['user_id']	= round($duty['user_id']);
		
		$this->load->model('user_model');
		if (! $this->user_model->is_valid_user_id($duty['user_id'])) {
			return false;
		}
		
		if (! $this->is_valid_vehicle($duty['vehicle'])) {
			return false;
		}
		
		if (! $this->is_valid_start_end($duty['start'], $duty['end'])) {
			$this->set_error('start_before_end');
			return false;
		}
		
		if (! $this->is_valid_comment($duty['comment'])) {
			$this->set_error('comment_too_long');
			return false;
		}
			
		return true;
	}
	
	/*
	 * Validates whether the number $vehicle represents a valid vehicle.
	 */
	public function is_valid_vehicle($vehicle) {
		if ($vehicle < 0 || $vehicle > 1) {
			return false;
		}
		return true;
	}
	
	/*
	 * Validates whether $time is a valid unix time.
	 */
	public function is_valid_unix_time($time) {
		if ($time < 0) {
			return false;
		}
		return true;
	}
	
	/*
	 * Validates wheter $comment is a valid duty comment.
	 */
	public function is_valid_comment($comment) {
		return is_string($comment) && strlen($comment) < 255;
	}
	
	/*
	 * Checks whether $start and $end are unix times and $start < $end.
	 */
	public function is_valid_start_end($start, $end) {
		if (! $this->is_valid_unix_time($start)) {
			return false;
		}
		
		if (! $this->is_valid_unix_time($end)) {
			return false;
		}
		
		if ($start >= $end) {
			return false;
		}
		
		return true;
	}
	
	/*
	 * Checks whether a duty with $duty_id already exists.
	 */
	public function is_existing_duty_id($duty_id) {
		return $this->get_duty($duty_id);
	}
	
	/*
	 * Checks whether the current user is allowed to edit $duty_id.
	 */
	public function is_allowed_to_edit($duty_id) {
		$old_duty = $this->get_duty($duty_id);
		
		if (! $this->ion_auth->is_admin()) {
			if ($old_duty['locked']) {
				return false;
			}
			
			if ($old_duty['user_id'] !== $this->ion_auth->get_user_id()) {
				return false;
			}
		}
		
		return true;
	}
	
	/*
	 * Determines whether the current user is allowed to add $duty.
	 */
	public function is_allowed_to_add($duty) {
		return $this->ion_auth->is_admin() || $duty['end'] >= time();
	}
	
	/*
	 * Checks whether $duty overlaps with another duty of the current
	 * user.
	 */
	public function is_not_conflicting($duty) {
		$conflicts = $this->get_conflicting_dutytimes($duty['user_id'], $duty['start'], $duty['end'], 
			isset($duty['id']) ? $duty['id'] : null);
		
		return $conflicts->num_rows() === 0;
	}
	
	public function are_overlap_free(&$duties) {
		$sort_by = array();
		foreach ($duties as $duty) {
			$sort_by[] = $duty['start'];
		}
		array_multisort($sort_by, SORT_ASC|SORT_NUMERIC, $duties);
		
		$end = array();
		foreach ($duties as $duty) {
			$prev_end = isset($end[$duty['user_id']]) ? $end[$duty['user_id']] : 0;
			if ($prev_end > $duty['start']) {
				return false;
			} else {
				$end[$duty['user_id']] = $duty['end'];
			}
		}
		
		return true;
	}
	
	public function set_error($error) {
		$this->errors[] = $error;
	}
	
	public function errors() {
		$msg = '';
		
		foreach ($this->errors as $error) {
			$msg .= $this->error_start_delimiter . $this->error_messages[$error] . $this->error_end_delimiter;
		}
		
		return $msg;
	}
	
}
