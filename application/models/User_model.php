<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class User_model extends CI_Model {
	
	/*
	 * Returns the full name of a user. The $user argument may be an
	 * object or an user id.
	 */
	public function get_full_name($user = null) {
		if (! is_object($user)) {
			$user = $this->ion_auth->user($user)->row();
		}
		$name = $user->first_name;
		if (! empty($user->last_name))
			$name .= ' ' . $user->last_name;
		if (empty($name)) // fallback to username
			$name = $this->session->username;
			
		return $name;
	}
	
	/*
	 * Returns an array of phone numbers and first and last names of all
	 * members of $groups. Users are excluded if any one of the
	 * aforementioned is unset.
	 * The result is sorted by last name.
	 */
	public function get_phone_numbers($groups = null) {
		$users		= $this->ion_auth->users($groups)->result();
		$numbers	= array(); // the resulting phone book
		$sort_by	= array(); // just the key by which to sort by
		
		foreach ($users as $user) {
			if (empty($user->last_name) ||
					empty($user->first_name) ||
					empty($user->phone)) {
				continue;
			}

			$numbers[] = array (
					'last_name'		=> $user->last_name,
					'first_name'	=> $user->first_name,
					'phone'			=> $user->phone,
				);
			$sort_by[] = $user->last_name;
		}
		
		array_multisort($sort_by, SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE, $numbers);
		
		return $numbers;
	}
	
	/*
	 * Returns the full names of all users with membership in $groups.
	 */
	public function get_user_names($groups = null) {
		$users 		= $this->ion_auth->users($groups)->result();
		$user_names	= array();
		
		foreach ($users as $user) {
			$user_names[$user->id] = $this->user_model->get_full_name($user);
		}
		asort($user_names);
		
		return $user_names;
	}
	
	public function is_valid_user_id($user_id) {
		$user = $this->ion_auth->user($user_id)->num_rows();
		
		return $user === 1;
	}
	
}
