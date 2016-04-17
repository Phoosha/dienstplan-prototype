<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('news_model');
		$this->load->model('user_model');
	}

	public function index()
	{		
		if (! $this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
			
		} else {
			// Set some common view settings
			$data['title']		= 'Willkommen';
			$data['menu']		= true;
			$data['menu_id']	= 'welcome';
			
			$data['message']	= $this->session->flashdata('message');
			$data['name']		= $this->user_model->get_full_name();
			$data['news']		= $this->news_model->get_news();
			
			// Resolve the authors user id to his full name
			foreach ($data['news'] as &$news_item) {
				$news_item['author_full'] = $this->user_model->get_full_name($news_item['author']);
			}		
		
			$this->load->template('welcome', $data);
		}
	}
}
