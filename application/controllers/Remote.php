<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Remote extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('plan_model');
		$this->load->helper('date');
		$this->load->config('dienstplan', true);
	}
	
	public function user($code = null) {
		if ($code === null) {
			show_error('You need to set the remote access code', 400);
			return;
		}
		
		$now			= time();
		$start_time		= $now - $this->config->item('calendar_start_dist', 'dienstplan');
		$end_time		= $now + $this->config->item('calendar_end_dist', 'dienstplan');
		
		$user_id		= $this->user_model->get_by_remote_code($code);
		if ($user_id === null) {
			showerror('This remote access code is not valid', 403);
			return;
		}
		
		$data['name']		= $this->user_model->get_full_name($user_id);
		$data['duties']		= $this->plan_model->get_dutytimes($start_time, $end_time, null, $user_id)->result_array();
		$data['vehicles']	= $this->plan_model->get_active_vehicles($start_time);
		$data['domain']		= $this->config->item('calendar_domain', 'dienstplan');
		
		$this->output->set_content_type('text/calendar');
		$this->load->view('ics.php', $data);
	}
	
}
