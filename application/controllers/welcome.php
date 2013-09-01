<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends REST_Controller
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function index_get($id = NULL)
	{	
		$this->load->view('index');
	}
}

