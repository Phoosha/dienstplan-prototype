<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This class provides access to the phone book, which is independent
 * from the users.
 */
class Phone_model extends CI_Model {
	
	public function __construct()
	{
		$this->load->database();
	}
	
	/*
	 * Returns all phone numbers together and sorted by the associated
	 * name.
	 */
	public function get_phone_numbers() {
		$this->db->select('name, phone');
		$this->db->order_by('name, phone');
		$numbers = $this->db->get('phonenumbers')->result_array();
		
		return $numbers;
	}
}

