<?php defined('BASEPATH') OR exit('No direct script access allowed');


class News_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	/*
	 * Returns an array of all releases and unexpired news entries, if
	 * $id is unset.
	 * Otherwise the news item with $id is returned.
	 */
	public function get_news($id = false)
	{
		if ($id === FALSE) {
			// get a list of all released and unexpired entries
			$this->db->order_by('release_on DESC, created_on DESC, id DESC');
			$this->db->where('release_on <', time());
			$this->db->group_start();
			$this->db->where('expires_on is null');
			$this->db->or_where('expires_on >', time());
			$this->db->group_end();
			$query = $this->db->get('news');
			
			return $query->result_array();
		}
		
		$query = $this->db->get_where('news', array('id' => $id));
		
		return $query->row_array();
	}
	
}
