<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function index()	{		
		if (! $this->ion_auth->logged_in()) {
			$this->session->set_userdata('return_to', current_url());
			redirect('auth/login', 'refresh');
			
		} else if (! $this->ion_auth->is_admin()) {
			// just redirect normal users who should not even get here
			redirect('/', 'refresh');
			
		} else {
			// Set some common view settings
			$data['title']		= 'Administration';
			$data['menu']		= true;
			$data['menu_id']	= 'admin';
		
			$this->load->template('administration', $data);
		}
	}

}
