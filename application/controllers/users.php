<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends REST_Controller
{
	public $_model = 'users';

    public $methods = array(
        'visitors_get'    => array(
			'params'		=> '!access_token, ?filter, ?filter_key, ?search_key',
            'description'   => 'Get paginated visitors.',
            'url_format'    => array('users/visitors'),
            'scope'         => ROLE_ADMIN
        ),
        'admins_get' => array(
            'params'        => '!access_token',
            'url_format'    => array('users/admins'),
            'description'   => 'Get paginated admins.',
            'scope'         => ROLE_SUPER_ADMIN
        ),
        'visit_post'    => array(
            'params'        => '!name, !country, !category, ?affiliation, ?contact',
            'url_format'    => array('users/visit'),
            'description'    => 'Visit.'
        ),
        'login_post'    => array(
            'params'        => '!name, !password',
            'url_format'    => array('users/login'),
            'description'    => 'Login.'
        ),
        'admins_post'=> array(
            'params'        => '!access_token, !name, !password',
            'url_format'    => array('users/admins'),
            'description'    => 'Add an admin.',
            'scope'         => ROLE_SUPER_ADMIN
        ),
        'admins_delete'=> array(
            'params'        => '!access_token',
            'url_format'    => array('users/admins/:id'),
            'description'    => 'Delete an admin.',
            'scope'         => ROLE_SUPER_ADMIN
        ),
        'logout_get'    => array(
            'params'        => '!access_token',
            'url_format'    => array('users/logout'),
            'description'    => 'Logout.',
            'scope'         => ROLE_ADMIN
        )
    );

    function __construct()
    {
        parent::__construct();
    }

    public function visit_post()
    {
		$required_fields	= array('name','country','category');
		$data				= $this->_require_fields($required_fields, $this->_post_args);
		$data['category']	= strtolower($data['category']);
		$data['country']	= strtolower($data['country']);
		$data['type']		= ROLE_VISITOR;
		$data				= $this->users_model->create($data);
		$this->response($data);
    }

	public function visitors_get()
	{
		$where = array('type' => 'visitor');
		
		if($this->get('filter') && $this->get('filter_key'))
		{
			$filter = $this->get('filter');
			self::_check_in_array($filter, array('country', 'category'), 'filter');
			$where[$filter] = $this->get('filter_key');
		}

		if($grouping === 'monthly'){
			$data[] = array('Month', 'Number of Visitors');
			for($i = date('m', now())-1; $i >= date('m', now()) - 12; $i--)
			{
				$where['date_created >='] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('n') - $i, 1));
				$where['date_created <'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('n') - $i+1, 1));
				$timestamp = mktime(0, 0, 0, date('n') - $i, 1);
				$data[] = array(date('M', $timestamp), $this->users_model->get_total_count(
						$where,
						$this->get('search_key'),
						$this->get('fields'),
						$this->get('page'),
						$this->get('limit'),
						$this->get('sort_field'),
						$this->get('sort_order')));
			}
		}
				
		$this->response($data);
	}

	public function admins_get()
	{
		$data = $this->users_model->get_all(
				array('type' => 'admin'),
				$this->get('search_key'),
				$this->get('fields'),
				$this->get('page'),
				$this->get('limit'),
				$this->get('sort_field'),
				$this->get('sort_order'));
				
		$this->response($data);
	}
	
	public function login_post()
	{
		$required_fields	= array('name', 'password');
		$data				= $this->_require_fields($required_fields, $this->_post_args);
		
		self::_check_strlen($data['password'], 6, 'password');
		$data				= $this->users_model->login($data);

		$this->response($data);
	}
	
	public function admins_post()
	{
		$required_fields	= array('name', 'password');
		$data				= $this->_require_fields($required_fields, $this->_post_args);

		self::_check_strlen($data['password'], 6, 'password');
		$this->users_model->unique_name($data['name']);
		
		$data['type']		= ROLE_ADMIN;
		$data['password']	= md5(PASSWORD_SALT . $data['password'] . PASSWORD_SALT);
		$data				= $this->users_model->create($data, $this->_fields);
		
		$this->response($data);
	}
	
	public function admins_delete($id = NULL)
	{
		$this->users_model->delete($id);
		$this->response(array('message' => 'User successfully deleted.'));
	}
	
	public function logout_get(){
		$this->users_model->update($this->user['id'], array('access_token' => NULL));
		$this->response(array('message' => 'Logout successful'));
	}
}