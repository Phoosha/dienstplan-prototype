<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {
    public function template($name, $data = array(), $return = false) {
        $data['_view'] = $name;
        $data['menu'] = (isset($data['menu'])) ? $data['menu'] : false;
		return $this->view('templates/html', $data, $return);
    }
}
