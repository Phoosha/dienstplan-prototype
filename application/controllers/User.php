<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function phonelist()
	{		
		if (! $this->ion_auth->logged_in()) {
			$this->session->set_userdata('return_to', current_url());
			redirect('auth/login', 'refresh');
			
		} else {
			// Set some common view settings
			$data['title']		= 'Telefonnummern';
			$data['menu']		= true;
			$data['menu_id']	= 'phone';
			
			$this->load->model('phone_model');
			$this->load->model('user_model');
			
			$data['numbers']	= $this->phone_model->get_phone_numbers();
			$data['users']		= $this->user_model->get_phone_numbers();
		
			$this->load->template('phonelist', $data);
		}
	}
	
	public function settings() {		
		if (! $this->ion_auth->logged_in()) {
			$this->session->set_userdata('return_to', current_url());
			redirect('auth/login', 'refresh');

		} else {
			// Set some common view settings
			$data['title']		= 'Mein Konto';
			$data['menu']		= true;
			$data['menu_id']	= 'settings';
			$data['user']		= $this->ion_auth->user($this->ion_auth->get_user_id())->row_array();
		
			$this->load->template('usersettings', $data);
		}
	}
}
