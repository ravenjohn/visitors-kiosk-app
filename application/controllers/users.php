<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends REST_Controller
{
	public $_model = 'users';

    public $methods = array(
        'visitors_get'    => array(
			'params'		=> '!access_token, ?start_date, ?end_date, ?filter, ?filter_key, ?search_key',
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
		
		if($this->get('start_date'))
		{
			self::_check_date($this->get('start_date'), 'start_date');
			$where['date_created >='] = $this->get('start_date');
		}
		
		if($this->get('end_date'))
		{
			self::_check_date($this->get('end_date'), 'end_date');
			$where['date_created <='] = $this->get('end_date');
		}
		
		$data = $this->users_model->get_all(
				$where,
				$this->get('search_key'),
				$this->get('fields'),
				$this->get('page'),
				$this->get('limit'),
				$this->get('sort_field'),
				$this->get('sort_order'));
				
		$this->response($data);
	}
}