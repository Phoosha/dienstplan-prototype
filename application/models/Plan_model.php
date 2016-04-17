<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_model extends CI_Model {
	
	public function __construct()
	{
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
		$this->db->where('active_on <=', $time);
		$this->db->limit(1);
		$this->db->order_by('active_on DESC, id DESC');
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
	public function get_conflicting_dutytimes($user_id, $start, $end) {	
		$this->_duty_select();
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
		
		if (! $this->validate_dutytime($duty)) {
			return false;
		}
		
		if (! $this->is_allowed_to_add($duty)) {
			return false;
		}
		
		if (! $this->is_not_conflicting($duty)) {
			return false;
		}
		
		return $this->db->insert('dutytimes', $duty);
	}
	
	/*
	 * Deletes the duty with $id.
	 */
	public function delete_dutytime($id) {
		$duty = $this->get_duty($id);
		if (! $this->ion_auth->is_admin()
				&& ($duty['locked']	|| $duty['user_id'] !== $this->ion_auth->get_user_id())) {
			return false;
		}
		
		return $this->db->delete('dutytimes', array('id' => $id));
	}
	
	/*
	 * Replaces the duty in the DB with $duty.
	 */
	public function replace_dutytime($duty) {
		if (! isset($duty['id']) || ! isset($duty['start']) || ! isset($duty['end'])
				|| ! isset($duty['vehicle']) || ! isset($duty['user_id'])) {
			return false;
		}
		
		$duty['start']		= (int) $duty['start'];
		$duty['end']		= (int) $duty['end'];
		$duty['vehicle']	= (int) $duty['vehicle'];
		$duty['user_id']	= (int) $duty['user_id'];
		
		$user		= $this->ion_auth->user($duty['user_id'])->row();
		$conflicts	= $this->get_conflicting_dutytimes($duty['user_id'], $duty['start'], $duty['end'])->num_rows();
		
		if (! $this->ion_auth->is_admin()
				&& ($old_duty['locked'] 
				|| $old_duty['user_id'] !== $this->ion_auth->get_user_id())
				|| ! $this->is_valid_vehicle($duty['vehicle'])
				|| ! $this->is_valid_unix_time($duty['start'])
				|| ! $this->is_valid_unix_time($duty['end'])
				|| ! isset($user->id) || $conflicts !== 1 || $duty['start'] >= $duty['end'])
		{
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
	
	public function validate_dutytime(&$duty, $check_id = false) {
		if (! isset($duty['start']) || ! isset($duty['end'])
				|| ! isset($duty['vehicle']) || ! isset($duty['user_id'])) {
			return false;
		}
		
		if ($check_id && ! isset($duty['id'])) {
			return false;
		}
		
		if ($check_id) {
			$duty['id']		= (int) $duty['id'];
		}
		$duty['start']		= (int) $duty['start'];
		$duty['end']		= (int) $duty['end'];
		$duty['vehicle']	= (int) $duty['vehicle'];
		$duty['user_id']	= (int) $duty['user_id'];
		
		$this->load->model('user_model');
		if (! $this->user_model->is_valid_user_id($duty['user_id'])) {
			return false;
		}
		
		if (! $this->is_valid_vehicle($duty['vehicle'])) {
			return false;
		}
		
		if (! $this->is_valid_unix_time($duty['start'])) {
			return false;
		}
		
		if (! $this->is_valid_unix_time($duty['end'])) {
			return false;
		}
		
		if ($duty['start'] >= $duty['end']) {
			return false;
		}
		
		return true;
	}
	
	public function is_allowed_to_edit($duty) {
		$old_duty = $this->get_duty($duty['id']);
		
		return $this->ion_auth->is_admin() || $old_duty['locked'] === false;
	}
	
	public function is_allowed_to_add($duty) {
		return $this->ion_auth->is_admin() || $duty['end'] >= time();
	}
	
	public function is_not_conflicting($duty) {
		$conflicts = $this->get_conflicting_dutytimes($duty['user_id'], $duty['start'], $duty['end'])->num_rows();
		
		return $conflicts === 0;
	}
	
}
