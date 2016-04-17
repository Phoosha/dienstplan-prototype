<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->library('form_validation');
	}
	
	public function login() {
		if ($this->ion_auth->logged_in()) {
			// skip login for already logged in users
			redirect('/', 'refresh');
		}
		
		// Set some common view settings
		$data['title']	= "Login";
		$data['menu']	= false;
		
		// just requiring user and password to be set is enough
		$this->form_validation->set_rules('user', 'Nutzername', 'required');
		$this->form_validation->set_rules('password', 'Passwort', 'required');
		
		if ($this->form_validation->run() === FALSE) {
			// no valid login data so display the login page
			
			// try to get a message about what went wrong if anything
			$data['message'] = validation_errors() ? 
				validation_errors() : $this->session->flashdata('message');
			
			$data['user'] = array(
				'name' => 'user',
				'id'    => 'user',
				'type'  => 'text',
				'placeholder' => 'Nutzername',
				'value' => $this->form_validation->set_value('user'),
			);
			$data['password'] = array(
				'name' => 'password',
				'id' => 'password',
				'placeholder' => 'Passwort',
				'type' => 'password',
			);
			
			$this->load->template('auth/login', $data);
			
		} else {
			// received complete login data: verify and redirect
			
			$user		= $this->input->post('user');
			$password	= $this->input->post('password');
			$remember	= (bool) $this->input->post('remember');
			$attempts	= $this->ion_auth->get_attempts_num($user);
			
			if ($this->ion_auth->is_time_locked_out($user)) {
				// blocking this user with a message
				
				$this->session->set_flashdata('message', '<p>Zugang temporär gesperrt. Versuche es später nochmal.</p>');
				redirect('auth/login', 'refresh');
				
			} else if ($this->ion_auth->login($user, $password, $remember)) {
				// successfull login
				
				// display a message if there were failed attempts
				if ($attempts > 0) {
					$last_login = date('j.n.Y G:i', $this->session->old_last_login);
					$message = "<p>Seit Ihrer letzten Anmeldung am $last_login, gab es $attempts fehlgeschlagene(n) Anmeldeversuch(e).</p>";
					$this->session->set_flashdata('message', $message);
				}
				
				// redirect to the original page if possible
				redirect(isset($_SESSION['return_to']) ? $_SESSION['return_to'] : '/', 'refresh');
				
			} else {
				// failed login: determine remaining tries and display an error
				
				// update attempts because this just was one
				$attempts		= $this->ion_auth->get_attempts_num($user);
				
				$max_attempts	= $this->config->item('maximum_login_attempts', 'ion_auth');
				// start showing from this threshold remaining attempts
				$th_attempts	= $this->config->item('threshold_attempts', 'ion_auth');
				$remaining_attempts = $max_attempts - $attempts;
				// fix this number being negative in some cases
				$remaining_attempts = ($remaining_attempts >= 0) ? $remaining_attempts : 0;
				
				// possibly create a message about the remaining attempts
				$message = '';
				if ($remaining_attempts <= $th_attempts) {
					$message = '<p>Noch '. $remaining_attempts .' verbleibende Login-Versuche</p>';
				}
				
				// redirect to the login page
				$this->session->set_flashdata('message', $this->ion_auth->errors() . $message);
				redirect('auth/login', 'refresh');
			}
		}
	}
	
	public function logout() {
		// log the user out
		$logout = $this->ion_auth->logout();

		// redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		redirect('auth/login', 'refresh');
	}
}
